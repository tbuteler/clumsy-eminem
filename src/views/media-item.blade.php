<div class="media-item">
    {{ HTML::image($media->previewPath(), null, array('data-src' => $media->path())) }}

    <div class="actions">
        <p>
            <strong>{{ trans('clumsy/eminem::all.item.path') }}:</strong> <a target="_blank" href="{{ $media->path() }}">{{ $media->path() }}</a><br>
            <strong>{{ trans('clumsy/eminem::all.item.mime') }}:</strong> {{ $media->mime_type }}
        </p>
    
        <button class="media-unbind btn btn-danger" data-id="{{ $media->association_id }}">{{ trans('clumsy/eminem::all.item.remove') }}</button>
    </div>
</div>