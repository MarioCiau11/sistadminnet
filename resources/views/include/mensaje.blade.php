@if(session()->has('message') )
    @if(session()->has('status') && !session()->get('status'))
    <script>
        swal({
            icon: "error",
            title: "Error",
            text: "{{ session()->get('message') }}",
            confirm: true,
            closeOnClickOutside: false,
            closeOnEsc: false,
            
        }).then((isConfirm) => {
                 if(isConfirm){
                    location.reload();
                 }
        });
    </script>
    @elseif (session()->has('status') && session()->get('status'))
    <script>
        swal({
            icon: "success",
            title: "Éxito",
            text: "{{ session()->get('message') }}",
            confirm: true,
            closeOnClickOutside: false,
            closeOnEsc: false,
        }).then((isConfirm) => {
                 if(isConfirm){
                    location.reload();
                 }
        });
    </script>
    @endif
@endif
