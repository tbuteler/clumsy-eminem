<div id="{{ $id }}-modal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{{ trans('clumsy/eminem::all.modal.close') }}</span></button>
                <h4 class="modal-title">{{ $label }}</h4>
            </div>

            <div class="modal-body">

                <ul class="nav nav-pills" role="tablist">
                    <li class="{{ !$media->isEmpty() ? 'active' : 'hidden' }}">
                        <a class="current-a" href="#{{ $id }}-current" role="tab" data-toggle="pill">
                            {{ trans('clumsy/eminem::all.modal.current') }}
                        </a>
                    </li>
                    <li class="{{ !$media->isEmpty() ? '' : 'active' }}">
                        <a class="upload-a" href="#{{ $id }}-upload" role="tab" data-toggle="pill">
                            {{ trans('clumsy/eminem::all.modal.upload') }}
                        </a>
                    </li>
                    <?php
                    /*
                    <li>
                        <a class="library-a" href="#{{ $id }}-library" role="tab" data-toggle="pill">
                            {{ trans('clumsy/eminem::all.modal.library') }}
                        </a>
                    </li>
                    */
                    ?>
                </ul>

                <div class="tab-content">

                    <div class="tab-pane fade {{ !$media->isEmpty() ? 'in active' : '' }}" id="{{ $id }}-current">
                        <div class="current-media">
                        @foreach ($media as $m)
                            @include('clumsy/eminem::media-item', array('media' => $m))
                        @endforeach
                        </div>
                    </div>

                    <div class="tab-pane fade {{ !$media->isEmpty() ? '' : 'in active' }}" id="{{ $id }}-upload">

                        <div class="drag-and-drop">
                            <div class="drag-text">
                                <h3 class="drag-title">{{ trans('clumsy/eminem::all.modal.drag') }}</h3>
                                <span class="separator">{{ trans('clumsy/eminem::all.modal.or') }}</span>
                                <button class="btn btn-default">{{ trans('clumsy/eminem::all.modal.select') }}</button>
                            </div>
                            <div class="drop-text">
                                <h3 class="drag-title">{{ trans('clumsy/eminem::all.modal.drop') }}</h3>
                            </div>
                        </div>

                    </div>

                    <?php
                    /*
                    <div class="tab-pane fade" id="{{ $id }}-library"></div>
                    */
                    ?>

                </div>
            </div>
        </div>
    </div>
</div>
