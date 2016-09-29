<div class="form-group fileupload-group">
    <label for="{{ $id }}">{{ $label }}</label>
    @if (count($comments))
        <a tabindex="0" role="button" data-toggle="popover" data-container="body" data-trigger="focus" title="{{ $label }}" data-html="true" data-content="{!! $comments !!}" class="fileupload-comments material-icons">&#xE88E;</a>
    @endif
    <div id="{{ $id }}" data-count="{{ $media ? $media->count() : 0 }}" class="fileupload thumbnail {{ $preview ? "preview-{$preview}" : '' }} {{ $media && !$media->isEmpty() ? '' : 'empty' }}">
        <div class="fileupload-wrapper">

            <div class="progress progress-striped active">
                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>

            @if ($media && $preview)
                {!! $preview === 'name' ? '<ol>' : '' !!}
                @foreach ($media as $m)
                    @include("clumsy/eminem::media-{$preview}", ['media' => $m])
                @endforeach
                {!! $preview === 'name' ? '</ol>' : '' !!}
            @endif

            <div class="placeholders">
                <i class="idle material-icons">&#xE2C6;</i>
                <i class="error material-icons">&#xE000;</i>
            </div>

        </div>

    </div>

    <input tabindex="-1" id="{{ $id }}-input" type="file" name="files[]" data-url="{{ $url }}" {{ $slot['allow_multiple'] ? 'multiple' : ''}}>

</div>
