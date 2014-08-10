<div class="media-item">
    {{ HTML::image($media->path) }}

    <div class="actions">
        <p>
            <strong>{{ trans('clumsy/eminem::all.item.path') }}:</strong> {{ url($media->path) }}<br>
            <strong>{{ trans('clumsy/eminem::all.item.mime') }}:</strong> {{ $media->mime_type }}
        </p>
    
        <button class="media-unbind btn btn-danger" data-id="{{ $media->id }}">{{ trans('clumsy/eminem::all.item.remove') }}</button>
    </div>
</div>