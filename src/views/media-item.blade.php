<div class="media-item">
    {{ HTML::image($media->previewPath(), null, array('data-src' => $media->path())) }}
    <div class="actions">
    	@if(isset($meta) && $meta != null)
            {{ Form::open(array('url' => route('media.save-meta', $media->association_id), 'class' => 'meta')) }}
            @foreach ($meta as $value => $name)
                <div class="form-group text">
                    <label for="{{ $value }}">{{ $name }}</label>
                    <input class="form-control" id="{{ $value }}" name="{{ $value }}" 
                    value="{{ $media->association_meta[$value] or '' }}" type="text">
                </div>
            @endforeach
        @endif
        <p>
            <strong>{{ trans('clumsy/eminem::all.item.path') }}:</strong> <a target="_blank" href="{{ $media->path() }}">{{ $media->path() }}</a><br>
            <strong>{{ trans('clumsy/eminem::all.item.mime') }}:</strong> {{ $media->mime_type }}
        </p>
    	
    	<button type="button" class="media-unbind btn btn-danger" data-id="{{ $media->association_id }}">{{ trans('clumsy/eminem::all.item.remove') }}</button>
        @if(isset($meta) && $meta != null)
        	<button class="media-save-meta btn btn-success" data-id="{{ $media->association_id }}">
        		{{ trans('clumsy/eminem::all.item.save') }}
                <i class="meta-save-active glyphicon glyphicon-pencil"></i>
                <i class="meta-save-loading glyphicon glyphicon glyphicon-refresh" style="display:none;"></i>
                <i class="meta-save-success glyphicon glyphicon-ok-sign" style="display:none;"></i>
        	</button>
            {{ Form::close() }}
        @endif
    </div>
</div>