@extends(config('saysay.crud.layout'))

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-xs-12">
            <div class="panel panel-flat">
                <div class="panel-heading">
                    <h6>
                        Translation Manager
                        <small class="display-block">Warning, translations are not visible until they are exported back to the app/lang file, using 'php artisan translation:export' command or publish button.</small>
                    </h6>
                    <div class="heading-elements">
                        @if(isset($currentLang))
                            <div class="btn-group heading-btn">
                                <button class="btn btn-success">{{ config('translatable.locales_name.' . $currentLang) }}</button>
                                <button class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                    <span class="caret"></span>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-right">
                                    @foreach(config('translatable.locales') as $_lang => $langName)
                                        @if($_lang != $currentLang)
                                            <li>
                                                <a href="{{ $form['selfLink'] . "?lang=" . $_lang }}">{{ $langName }}</a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="panel-body">
                    <div class="alert alert-success success-import" style="display:none;">
                        <p>Done importing, processed <strong class="counter">N</strong> items! Reload this page to refresh the groups!</p>
                    </div>
                    <div class="alert alert-success success-find" style="display:none;">
                        <p>Done searching for translations, found <strong class="counter">N</strong> items!</p>
                    </div>
                    <div class="alert alert-success success-publish" style="display:none;">
                        <p>Done publishing the translations for group '<?= $group ?>'!</p>
                    </div>
                    <?php if(Session::has('successPublish')) : ?>
                    <div class="alert alert-info">
                        <?php echo Session::get('successPublish'); ?>
                    </div>
                    <?php endif; ?>
                    <p>
                    <?php if(!isset($group)) : ?>
                    <form class="form-inline form-import" method="POST" action="<?= action('\Barryvdh\TranslationManager\Controller@postImport') ?>" data-remote="true" role="form">
                        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                        <select name="replace" class="form-control">
                            <option value="0">Append new translations</option>
                            <option value="1">Replace existing translations</option>
                        </select>
                        <button type="submit" class="btn btn-success"  data-disable-with="Loading..">Import groups</button>
                    </form>
                    <form class="form-inline form-find" method="POST" action="<?= action('\Barryvdh\TranslationManager\Controller@postFind') ?>" data-remote="true" role="form" data-confirm="Are you sure you want to scan you app folder? All found translation keys will be added to the database.">
                        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                        <p></p>
                        <button type="submit" class="btn btn-info" data-disable-with="Searching.." >Find translations in files</button>
                    </form>
                    <?php endif; ?>
                    <?php if(isset($group)) : ?>
                    <form class="form-inline form-publish" method="POST" action="<?= action('\Barryvdh\TranslationManager\Controller@postPublish', $group) ?>" data-remote="true" role="form" data-confirm="Are you sure you want to publish the translations group '<?= $group ?>? This will overwrite existing language files.">
                        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                        <button type="submit" class="btn btn-info" data-disable-with="Publishing.." >Publish translations</button>
                        <a href="<?= action('\Barryvdh\TranslationManager\Controller@getIndex') ?>" class="btn btn-default">Back</a>
                    </form>
                    <?php endif; ?>
                    </p>
                    <form role="form">
                        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                        <div class="form-group">
                            <select name="group" id="group" class="form-control group-select">
                                <?php foreach($groups as $key => $value): ?>
                                <option value="<?= $key ?>"<?= $key == $group ? ' selected':'' ?>><?= $value ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                    <?php if($group): ?>
                    <form action="<?= action('\Barryvdh\TranslationManager\Controller@postAdd', array($group)) ?>" method="POST"  role="form">
                        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                        <div class="row">
                            <div class="col-lg-10">
                                <input class="form-control" rows="3" name="keys" placeholder="Add 1 key per line, without the group prefix"/>
                            </div>
                            <div class="col-lg-2">
                                <input type="submit" value="Add keys" class="btn btn-primary">
                            </div>
                        </div>
                    </form>
                    <hr>
                    <h4>Total: <?= $numTranslations ?>, changed: <?= $numChanged ?></h4>
                    <table class="table">
                        <thead>
                        <tr>
                            <th width="15%">Key</th>
                            <?php foreach($locales as $locale): ?>
                            <th><?= $locale ?></th>
                            <?php endforeach; ?>
                            <?php if($deleteEnabled): ?>
                            <th>&nbsp;</th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>

                        <?php foreach($translations as $key => $translation): ?>
                        <tr id="<?= $key ?>">
                            <td><?= $key ?></td>
                            <?php foreach($locales as $locale): ?>
                            <?php $t = isset($translation[$locale]) ? $translation[$locale] : null?>

                            <td>
                                <a href="#edit" class="editable status-<?= $t ? $t->status : 0 ?> locale-<?= $locale ?>" data-locale="<?= $locale ?>" data-name="<?= $locale . "|" . $key ?>" id="username" data-type="textarea" data-pk="<?= $t ? $t->id : 0 ?>" data-url="<?= $editUrl ?>" data-title="Enter translation"><?= $t ? htmlentities($t->value, ENT_QUOTES, 'UTF-8', false) : '' ?></a>
                            </td>
                            <?php endforeach; ?>
                            <?php if($deleteEnabled): ?>
                            <td>
                                <a href="<?= action('\Barryvdh\TranslationManager\Controller@postDelete', [$group, $key]) ?>" class="delete-key" data-confirm="Are you sure you want to delete the translations for '<?= $key ?>?"><span class="glyphicon glyphicon-trash"></span></a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>

                        </tbody>
                    </table>
                    <?php else: ?>
                    <p>Choose a group to display the group translations. If no groups are visible, make sure you have run the migrations and imported the translations.</p>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    jQuery(document).ready(function($){

        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                console.log('beforesend');
                settings.data += "&_token=<?= csrf_token() ?>";
            }
        });

        $('.editable').editable().on('hidden', function(e, reason){
            var locale = $(this).data('locale');
            if(reason === 'save'){
                $(this).removeClass('status-0').addClass('status-1');
            }
            if(reason === 'save' || reason === 'nochange') {
                var $next = $(this).closest('tr').next().find('.editable.locale-'+locale);
                setTimeout(function() {
                    $next.editable('show');
                }, 300);
            }
        });

        $('.group-select').on('change', function(){
            var group = $(this).val();
            if (group) {
                window.location.href = '<?= action('\Barryvdh\TranslationManager\Controller@getView') ?>/'+$(this).val();
            } else {
                window.location.href = '<?= action('\Barryvdh\TranslationManager\Controller@getIndex') ?>';
            }
        });

        $("a.delete-key").click(function(event){
            event.preventDefault();
            var row = $(this).closest('tr');
            var url = $(this).attr('href');
            var id = row.attr('id');
            $.post( url, {id: id}, function(){
                row.remove();
            } );
        });

        $('.form-import').on('ajax:success', function (e, data) {
            $('div.success-import strong.counter').text(data.counter);
            $('div.success-import').slideDown();
        });

        $('.form-find').on('ajax:success', function (e, data) {
            $('div.success-find strong.counter').text(data.counter);
            $('div.success-find').slideDown();
        });

        $('.form-publish').on('ajax:success', function (e, data) {
            $('div.success-publish').slideDown();
        });

    })
</script>
@endpush
