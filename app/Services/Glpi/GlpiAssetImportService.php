<?php

namespace App\Services\Glpi;

use App\Models\GlpiInstance;

/**
 * Maps the CCITI's own standard CSV template (public/templates/
 * glpi_asset_import_template.csv - the client adapts whatever inventory
 * export they already have to this format, instead of this service trying
 * to special-case every possible source tool) onto GLPI's asset model, via
 * GlpiApiClient (REST API v2).
 *
 * Three-step flow (mirrors NFSeImportController's analyze/store pattern):
 *   parseCsv()  - turns the uploaded file into plain arrays, no GLPI calls.
 *   analyze()   - read-only preview (counts, which dropdowns already exist
 *                 vs would be created, row-level validation errors).
 *   import()    - the real thing: find-or-create every dependency, then
 *                 find-by-otherserial-or-create the asset itself.
 */
class GlpiAssetImportService
{
    /**
     * "Tipo de Ativo" (CSV, PT-BR) -> GLPI itemtype. Matched
     * case-insensitively (see resolveItemtype()) - a spreadsheet filled by
     * hand will inevitably have "computador"/"COMPUTADOR" typed somewhere.
     */
    private const TYPE_MAP = [
        'Computador' => 'Computer',
        'Monitor' => 'Monitor',
        'Telefone' => 'Phone',
        'Ponto de Acesso' => 'NetworkEquipment',
        'Chip' => 'Line',
    ];

    /**
     * GLPI itemtype -> its own *Type/*Model dropdown itemtypes. `Line`
     * only has a type dropdown (LineType) - GLPI has no "line model".
     */
    private const TYPE_DROPDOWNS = [
        'Computer' => ['type' => 'ComputerType', 'model' => 'ComputerModel'],
        'Monitor' => ['type' => 'MonitorType', 'model' => 'MonitorModel'],
        'Phone' => ['type' => 'PhoneType', 'model' => 'PhoneModel'],
        'NetworkEquipment' => ['type' => 'NetworkEquipmentType', 'model' => 'NetworkEquipmentModel'],
        'Line' => ['type' => 'LineType', 'model' => null],
    ];

    public function __construct(private GlpiApiClient $client)
    {
    }

    public static function forInstance(GlpiInstance $instance): self
    {
        return new self(new GlpiApiClient($instance));
    }

    public static function recognizedTypes(): array
    {
        return array_keys(self::TYPE_MAP);
    }

    private function resolveItemtype(string $tipo): ?string
    {
        foreach (self::TYPE_MAP as $label => $itemtype) {
            if (mb_strtolower($label) === mb_strtolower($tipo)) {
                return $itemtype;
            }
        }

        return null;
    }

    /**
     * @return array{rows: array, errors: array<int, string>}
     */
    public function parseCsv(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException('Não foi possível abrir o arquivo CSV.');
        }

        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        // Excel/Sheets no Windows costuma salvar "CSV" como Windows-1252/
        // ISO-8859-1, não UTF-8, quando o usuário não escolhe explicitamente
        // "CSV UTF-8" - sem essa conversão, todo acento (nome de
        // localização, responsável...) vira um emaranhado de caracteres
        // (mojibake) nos dados, mesmo já com o cabeçalho sem acento nenhum.
        $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);
        if ($encoding !== false && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new \RuntimeException('CSV vazio ou sem cabeçalho.');
        }
        // Cabeçalho normalizado (minúsculo/trim) - tolera "Tipo_Ativo",
        // "TIPO_ATIVO" etc. além do "tipo_ativo" documentado.
        $header = array_map(fn ($h) => mb_strtolower(trim((string) $h)), $header);

        $rows = [];
        $errors = [];
        $lineNumber = 1;

        while (($fields = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (count($fields) === 1 && trim((string) $fields[0]) === '') {
                continue; // linha em branco
            }

            if (count($fields) !== count($header)) {
                $errors[$lineNumber] = 'Número de colunas não bate com o cabeçalho.';

                continue;
            }

            $raw = array_combine($header, $fields);

            $tipo = trim($raw['tipo_ativo'] ?? '');
            $nome = trim($raw['nome'] ?? '');
            $recursoId = trim($raw['codigo_referencia'] ?? '');

            if ($nome === '') {
                $errors[$lineNumber] = 'Sem "nome".';

                continue;
            }

            if ($recursoId === '') {
                $errors[$lineNumber] = 'Sem "codigo_referencia" - é essa coluna que impede duplicar o ativo se o CSV for importado de novo.';

                continue;
            }

            $itemtype = $this->resolveItemtype($tipo);
            if ($itemtype === null) {
                $errors[$lineNumber] = "\"tipo_ativo\" \"{$tipo}\" não reconhecido - use um de: " . implode(', ', self::recognizedTypes());

                continue;
            }

            $rows[] = [
                'line' => $lineNumber,
                'recurso_id' => $recursoId,
                'itemtype' => $itemtype,
                'tipo_original' => $tipo,
                'nome' => $nome,
                'ativo' => trim($raw['numero_serie'] ?? ''),
                'status' => trim($raw['status'] ?? ''),
                'proprietario' => trim($raw['responsavel'] ?? ''),
                'localizacao' => trim($raw['localizacao'] ?? ''),
                'fabricante' => trim($raw['fabricante'] ?? ''),
                'modelo' => trim($raw['modelo'] ?? ''),
                'patrimonio' => trim($raw['patrimonio'] ?? ''),
                'link_assinei' => trim($raw['link_termo'] ?? ''),
                'termo_assinado' => mb_strtolower(trim($raw['termo_assinado'] ?? '')) === 'sim',
            ];
        }

        fclose($handle);

        return ['rows' => $rows, 'errors' => $errors];
    }

    /**
     * Read-only preview: never creates anything in GLPI. Reports, per
     * dropdown category, how many of the CSV's distinct values already
     * exist vs would need to be created.
     */
    public function analyze(array $rows): array
    {
        $byType = [];
        foreach ($rows as $row) {
            $byType[$row['itemtype']] = ($byType[$row['itemtype']] ?? 0) + 1;
        }

        $locations = $this->uniqueNonBlank($rows, 'localizacao');
        $manufacturers = $this->uniqueNonBlank($rows, 'fabricante');
        $states = $this->uniqueNonBlank($rows, 'status');

        $modelsByItemtype = [];
        foreach ($rows as $row) {
            if ($row['modelo'] === '') {
                continue;
            }
            $modelsByItemtype[$row['itemtype']][$row['modelo']] = true;
        }

        return [
            'total_rows' => count($rows),
            'by_type' => $byType,
            'dropdowns' => [
                'Location' => $this->checkExisting('Location', $locations),
                'Manufacturer' => $this->checkExisting('Manufacturer', $manufacturers),
                'State' => $this->checkExisting('State', $states),
                ...$this->modelDropdownPreview($modelsByItemtype),
            ],
            'signed_terms' => count(array_filter($rows, fn ($r) => $r['termo_assinado'])),
            'with_link' => count(array_filter($rows, fn ($r) => $r['link_assinei'] !== '')),
        ];
    }

    /**
     * The real import. Returns a per-row outcome report - never lets one
     * bad row abort the rest of the batch.
     *
     * @return array{created: int, updated: int, errors: array<int, string>, items: array<int, array>}
     */
    public function import(array $rows): array
    {
        $created = 0;
        $updated = 0;
        $errors = [];
        // Um registro por linha processada com sucesso - pra quem importou
        // poder ver exatamente quais ativos foram criados/atualizados no
        // GLPI, não só a contagem.
        $items = [];

        // Dropdown ids resolved once per distinct value and reused for
        // every row that shares it, instead of round-tripping the API
        // again for the 2000th monitor at the same location.
        $locationIds = [];
        $manufacturerIds = [];
        $stateIds = [];
        $typeIds = [];
        $modelIds = [];

        foreach ($rows as $row) {
            try {
                $itemtype = $row['itemtype'];
                $dropdowns = self::TYPE_DROPDOWNS[$itemtype];
                // `Line` has no serial/otherserial/contact field at all (its
                // GLPI schema is just name/status/location/type/entity/...),
                // so the InvGate id can't live in otherserial there - it
                // rides along in `name` instead, and that's what idempotency
                // matches on for this one itemtype.
                $isLine = $itemtype === 'Line';
                $invgateTag = 'INVGATE-' . $row['recurso_id'];

                $data = [
                    'name' => $isLine ? "{$row['nome']} ({$invgateTag})" : $row['nome'],
                    'comment' => $this->buildComment($row),
                ];

                if (! $isLine) {
                    $data['serial'] = $row['ativo'] ?: null;
                    $data['otherserial'] = $invgateTag;
                    $data['contact'] = $row['proprietario'] ?: null;
                }

                if ($row['localizacao'] !== '') {
                    $data['location'] = ['id' => $locationIds[$row['localizacao']]
                        ??= $this->client->findOrCreateDropdown('Location', $row['localizacao'])];
                }

                if (! $isLine && $row['fabricante'] !== '') {
                    $data['manufacturer'] = ['id' => $manufacturerIds[$row['fabricante']]
                        ??= $this->client->findOrCreateDropdown('Manufacturer', $row['fabricante'])];
                }

                if ($row['status'] !== '') {
                    $data['status'] = ['id' => $stateIds[$row['status']]
                        ??= $this->client->findOrCreateDropdown('State', $row['status'])];
                }

                $typeKey = "{$itemtype}:{$row['tipo_original']}";
                $data['type'] = ['id' => $typeIds[$typeKey]
                    ??= $this->client->findOrCreateDropdown($dropdowns['type'], $row['tipo_original'])];

                if ($dropdowns['model'] !== null && $row['modelo'] !== '') {
                    $modelKey = "{$itemtype}:{$row['modelo']}";
                    $data['model'] = ['id' => $modelIds[$modelKey]
                        ??= $this->client->findOrCreateDropdown($dropdowns['model'], $row['modelo'])];
                }

                $existing = $isLine
                    ? $this->client->findOneByField('Management/Line', 'name', $data['name'])
                    : $this->client->findAssetByOtherSerial($itemtype, $data['otherserial']);

                if ($existing !== null) {
                    $this->client->updateAsset($itemtype, $existing['id'], $data);
                    $updated++;
                    $items[] = [
                        'line' => $row['line'],
                        'itemtype' => $itemtype,
                        'nome' => $row['nome'],
                        'action' => 'updated',
                        'glpi_id' => $existing['id'],
                    ];
                } else {
                    $createdAsset = $this->client->createAsset($itemtype, $data);
                    $created++;
                    $items[] = [
                        'line' => $row['line'],
                        'itemtype' => $itemtype,
                        'nome' => $row['nome'],
                        'action' => 'created',
                        'glpi_id' => $createdAsset['id'] ?? null,
                    ];
                }
            } catch (\Throwable $e) {
                $errors[$row['line']] = $e->getMessage();
            }
        }

        return ['created' => $created, 'updated' => $updated, 'errors' => $errors, 'items' => $items];
    }

    private function buildComment(array $row): ?string
    {
        if (! $row['termo_assinado']) {
            return null;
        }

        // O plugin termodocs (autoria própria da CCITI) tem a estrutura
        // certa pra registrar isso como um aceite de verdade
        // (signature_provider/external_reference) mas suas classes não são
        // alcançáveis pela API v2 do GLPI hoje (só controllers do core têm
        // rota - ver o plano) - por ora fica só como anotação legível no
        // próprio ativo.
        return $row['link_assinei'] !== ''
            ? "Termo de entrega assinado (Assinei): {$row['link_assinei']}"
            : 'Termo de entrega assinado (Assinei) - sem link registrado no CSV de origem.';
    }

    private function uniqueNonBlank(array $rows, string $field): array
    {
        return array_values(array_unique(array_filter(array_map(fn ($r) => $r[$field], $rows), fn ($v) => $v !== '')));
    }

    /**
     * @return array{existing: int, new: int, values: array}
     */
    private function checkExisting(string $itemtype, array $values): array
    {
        $existing = 0;

        foreach ($values as $value) {
            // Read-only preview - a single value the API chokes on (seen in
            // practice: RSQL parsing issues) shouldn't abort the whole
            // analyze() over one of 2000+ rows. Counted as "new" (the
            // conservative guess) and the real import() attempts it anyway.
            try {
                if ($this->client->findOneByField("Dropdowns/{$itemtype}", 'name', $value) !== null) {
                    $existing++;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return ['existing' => $existing, 'new' => count($values) - $existing, 'values' => $values];
    }

    private function modelDropdownPreview(array $modelsByItemtype): array
    {
        $preview = [];

        foreach ($modelsByItemtype as $itemtype => $models) {
            $dropdown = self::TYPE_DROPDOWNS[$itemtype]['model'] ?? null;
            if ($dropdown === null) {
                continue;
            }

            $preview[$dropdown] = $this->checkExisting($dropdown, array_keys($models));
        }

        return $preview;
    }
}
