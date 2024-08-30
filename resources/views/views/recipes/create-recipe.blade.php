@extends('layout.master')

@push('plugin-styles')
  <link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/jquery-tags-input/jquery.tagsinput.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/dropzone/dropzone.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/dropify/css/dropify.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/pickr/themes/classic.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Forms</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add Your Recipe</li>
  </ol>
</nav>

<div class="row">
  <div class="w-75 m-auto grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
{{--          id="signupForm"--}}
        <h4 class="card-title">Add Your Recipe</h4>
{{--        <p class="text-muted mb-3">Read the <a href="https://jqueryvalidation.org/" target="_blank"> Official jQuery Validation Documentation </a>for a full list of instructions and other options.</p>--}}
        <form action="{{ route('recipes.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
          <div class="mb-3">
            <label for="name" class="form-label">Title</label>
            <input id="name" class="form-control" name="title" type="text">
          </div>
          <div class="mb-3">
            <label for="image" class="form-label">Image</label>
            <input id="image" class="form-control" name="image" type="file">
          </div>
            <div id="ingredients-container" class="mb-3">
                <label class="form-label">Ingrédients</label>
                <div class="ingredient-item">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="ingredients[0][name]" placeholder="Nom de l'ingrédient" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="ingredients[0][quantity]" placeholder="Quantité" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button type="button" class="btn btn-secondary" id="add-ingredient"><i data-feather="plus"></i>Ajouter un ingrédient</button>
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <div>
                    @foreach($categories as $category)
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="categories[]" class="form-check-input" id="checkInline{{$category->id}}" value="{{ $category->id }}">
                        <label class="form-check-label" for="checkInline{{$category->id}}">
                            {{$category->name}}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="mb-3">
                <label for="exampleFormControlTextarea1" class="form-label">Summary</label>
                <textarea class="form-control" id="exampleFormControlTextarea1" name="summary" rows="5"></textarea>
            </div>

          <button class="btn btn-primary" type="submit">Submit</button>
        </form>
      </div>
    </div>
  </div>

</div>


@endsection



@push('plugin-scripts')
  <script src="{{ asset('assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/typeahead-js/typeahead.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/jquery-tags-input/jquery.tagsinput.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/dropzone/dropzone.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/dropify/js/dropify.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/pickr/pickr.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/moment/moment.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/flatpickr/flatpickr.min.js') }}"></script>
@endpush

@push('custom-scripts')
  <script src="{{ asset('assets/js/form-validation.js') }}"></script>
  <script src="{{ asset('assets/js/bootstrap-maxlength.js') }}"></script>
  <script src="{{ asset('assets/js/inputmask.js') }}"></script>
  <script src="{{ asset('assets/js/select2.js') }}"></script>
  <script src="{{ asset('assets/js/typeahead.js') }}"></script>
  <script src="{{ asset('assets/js/tags-input.js') }}"></script>
  <script src="{{ asset('assets/js/dropzone.js') }}"></script>
  <script src="{{ asset('assets/js/dropify.js') }}"></script>
  <script src="{{ asset('assets/js/pickr.js') }}"></script>
  <script src="{{ asset('assets/js/flatpickr.js') }}"></script>
  <script src="{{ asset('assets/js/script.js') }}"></script>

@endpush

