@extends('layouts.app')

@section('title', 'Welcome Page')

@section('content')
    <div class="container">
        <form id="qaForm" class="form-horizontal">
            @csrf
            <div class="form-group">
                <label class="control-label col-sm-2" for="question">Question:</label>
                <div class="col-sm-10">
                    <textarea class="form-control" rows="3" id="question" name="question"></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-2" for="answer">Answer:</label>
                <div class="col-sm-10">
                    <textarea class="form-control" rows="3" id="answer" name="answer"></textarea>
                </div>
            </div>
            <br>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="button" id="sendBtn" class="btn btn-secondary">Submit</button>
                </div>
            </div>
        </form>

        {{-- Loading animation --}}
        <div id="loading" class="text-center mt-3" style="display:none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Please wait...</p>
        </div>

        {{-- Natija ko‘rinadigan joy --}}
        <div id="result" class="alert alert-info mt-3" style="display:none;"></div>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    $('#sendBtn').on('click', function(){
        // console.log("working well");

        $('#loading').show();
        $('#result').hide();

        $.ajax({
            url: "{{ url('/score-qa') }}",
            type: "POST",
            data: $('#qaForm').serialize(),
            success: function(response){
                console.log(response);
                $('#loading').hide();
                $('#result').show().html("<b>Serverdan javob:</b> " + response.score);
            },
            error: function(){
                $('#loading').hide();
                $('#result').show().html("<b style='color:red'>Xatolik yuz berdi!</b>");
            }
        });

    });
});
</script>
@endpush
