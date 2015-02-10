<div class="form-group fileupload-group">
    {{ Form::label($id, $label) }}
    <div id="{{ $id }}" class="fileupload {{ $media && !$media->isEmpty() ? '' : 'empty' }}">
        <div class="fileupload-wrapper">

            <div class="progress progress-striped active">
                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>

            @if ($media)

                @foreach ($media as $m)

                    {{ HTML::image($m->previewPath(), $id, array('data-src' => $m->path())) }}

                @endforeach

            @endif

            <div class="placeholders">
                <div class="idle glyphicon glyphicon-cloud-upload"></div>
                <div class="error glyphicon glyphicon-exclamation-sign"></div>
            </div>

        </div>
    </div>
    
    <input id="{{ $id }}-input" type="file" name="files[]" data-url="{{ $url }}" {{ $allow_multiple ? 'multiple' : ''}}>

</div>