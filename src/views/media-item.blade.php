<div class="media-item">
    @include('clumsy/eminem::media-image', compact('media'))
    <div class="actions">
        @if (isset($meta) && $meta != null)
        <form method="POST" action="{{ route('eminem.save-meta', $media->bindId) }}" accept-charset="UTF-8" class="meta">
            {!! csrf_field() !!}
            @foreach ($meta as $value => $name)
            <div class="form-group text">
                <label for="{{ $value }}">{{ $name }}</label>
                <input class="form-control" id="{{ $value }}" name="{{ $value }}"
                value="{{ $media->getPivotMeta($value) }}" type="text">
            </div>
            @endforeach
        @endif
        <p>
            <strong>{{ trans('clumsy/eminem::all.item.path') }}:</strong> <a target="_blank" href="{{ $media }}">{{ $media }}</a><br>
            <strong>{{ trans('clumsy/eminem::all.item.mime') }}:</strong> {{ $media->mime_type }}
        </p>

        <button type="button" class="media-unbind btn btn-danger" data-id="{{ $media->bindId }}">{{ trans('clumsy/eminem::all.item.remove') }}</button>
        @if(isset($meta) && $meta != null)
            <button class="media-save-meta btn btn-success" data-id="{{ $media->bindId }}">
                {{ trans('clumsy/eminem::all.item.save') }}
                <i class="meta-save-active glyphicon glyphicon-pencil"></i>
                <i class="meta-save-loading glyphicon glyphicon glyphicon-refresh" style="display:none;"></i>
                <i class="meta-save-success glyphicon glyphicon-ok-sign" style="display:none;"></i>
            </button>
        </form>
        @endif
    </div>
</div>
