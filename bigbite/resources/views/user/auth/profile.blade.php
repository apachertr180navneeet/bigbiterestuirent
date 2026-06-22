@extends('user.layouts.app')
@section('style')
<style>
 .user-image{
    height: 70px;
    width: auto;
    border:1px dotted lightgray;
    padding:4px;
    margin: 0 auto;
 }  
</style>
@endsection 
@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <h5 class="py-2 mb-2">
        <span class="text-primary fw-light">My Profile</span>
    </h5>
    <div class="row">
        <div class="col-xl-6 col-lg-6">
            <div class="card profile-card">
                <div class="card-body  pb-5">
                    <form action="{{ route('user.update.profile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Name*</label>
                                    <input type="text" id="" name="name" class="form-control" placeholder="Enter First Name" value="{{old('name',$user->name)}}" readonly>
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Mobile*</label>
                                    <input type="tel" name="mobile" class="form-control" placeholder="Enter Mobile" value="{{ old('mobile',$user->mobile) }}" readonly>
                                    @error('mobile')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                       
                        {{--  <div class="pt-4">
                            <div class="col-md-12 submit-btn">
                                <button type="submit" class="btn btn-primary">Save</button> 
                            </div>
                        </div>  --}}
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
    $(".timezone").select2().on('select2:opening', function(e) {
        $(this).data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', 'Search your timezone')
    })
</script> 
@endsection
