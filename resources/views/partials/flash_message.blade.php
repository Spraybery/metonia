@if(session('flash_success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="icon-checkmark3 mr-2"></i> {{ session('flash_success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('flash_danger'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="icon-blocked mr-2"></i> {{ session('flash_danger') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="icon-warning2 mr-2"></i> Please correct the following errors:</strong>
        <ul class="mb-0 mt-1 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
