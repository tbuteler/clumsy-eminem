<div class="media-item">
    {{ HTML::image($media->path) }}

    <div class="actions">
        <p>
            <strong>Path:</strong> {{ url($media->path) }}<br>
            <strong>MIME type:</strong> {{ $media->mime_type }}
        </p>
    
        <button class="media-unbind btn btn-danger" data-id="{{ $media->id }}">Remove image</button>
    </div>
</div>