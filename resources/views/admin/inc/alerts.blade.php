@if (Session::has('success'))
<div class="toastr-message" data-type="success">{{ session('success') }}</div>
@endif

@if (Session::has('error'))
<div class="toastr-message" data-type="error">{{ session('error') }}</div>
@endif

@if (Session::has('info'))
<div class="toastr-message" data-type="info">{{ session('info') }}</div>
@endif

@if (Session::has('warning'))
<div class="toastr-message" data-type="warning">{{ session('warning') }}</div>
@endif

<script>
    $(document).ready(function() {
        $('.toastr-message').each(function() {
            var messageType = $(this).data('type');
            var messageText = $(this).text();
            // The theme's toastr.min.js defaults to positionClass
            // "toast-top-center" (a full-width bar pinned over the header -
            // fine for a short line, but it swallows the header entirely
            // for a long message like an API error dump). closeButton+timeOut
            // were both off, so a stuck toast had no way to go away either.
            // Errors stay up longer (and are closable) since they're the
            // ones likely to be long; success/info still auto-dismiss fast.
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "extendedTimeOut": 1000,
                "timeOut": messageType === 'error' ? 15000 : 4000,
                // toastr's default toastClass ("toast") collides with the
                // theme's own Bootstrap 5 .toast component
                // (assets/scss/components/_toasts.scss), which forces
                // background-color: rgba(255,255,255,.85) !important - that
                // !important beats toastr's own (non-!important)
                // .toast-success/.toast-error/etc background colors, so
                // every toast rendered nearly white while its text stayed
                // white too (toastr's #toast-container>div rule), making it
                // unreadable. Renaming the class sidesteps the theme's rule
                // entirely - box layout still comes from the ID selector
                // #toast-container>div, unaffected by this.
                "toastClass": "toastr-box",
            };

            switch (messageType) {
                case 'success':
                    toastr.success(messageText);
                    break;
                case 'error':
                    toastr.error(messageText);
                    break;
                case 'info':
                    toastr.info(messageText);
                    break;
                case 'warning':
                    toastr.warning(messageText);
                    break;
                default:
                    toastr.info(messageText);
            }
        });
    });
</script>
