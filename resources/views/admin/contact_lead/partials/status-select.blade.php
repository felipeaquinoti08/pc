@php $canEdit = auth()->user()->can('update', $row); @endphp
<select
    class="form-select form-select-sm contact-lead-status-select badge {{ $row->statusBadgeClass() }}"
    style="width:auto;"
    data-route="{{ route('admin.contact_lead.status', $row->id) }}"
    @disabled(! $canEdit)
>
    @foreach (\App\Models\ContactLead::statuses() as $value => $label)
        <option value="{{ $value }}" @selected($row->status === $value)>{{ $label }}</option>
    @endforeach
</select>
