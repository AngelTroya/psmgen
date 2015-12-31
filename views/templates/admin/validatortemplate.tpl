{**   
* Module PSMGen (v1.0) 
* 
*  @author    Angel Maria de Troya de la Vega <angelmaria87@gmail.com>
*  @copyright 2014 
*  @license   CopyRight
**}

<h1> {l s='PrestaShop Module Validator' mod='psmgen'} </h1>


<form action="{$controller_link|escape:"htmlall"}&token={$smarty.get.token|escape:"htmlall"}" method="post" enctype="multipart/form-data">

    <div class="panel" id="fieldset_0">
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3 required">
                    {l s= 'Import zip module' mod='psmgen'}
                </label>
                <input type="file" name="nombre_archivo_cliente" accept=".zip"/><br /><br />


                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Validate isset' mod='psmgen'}
                    </label>

                    <div class="col-lg-9 ">
                        <div class="radio radioCheck">
                            <label><input type="radio" name="function_isset" id="fi_active_on" value="1" checked="checked"/>{l s='Yes' mod='psmgen'}</label>
                        </div>
                        <div class="radio radioCheck">
                            <label><input type="radio" name="function_isset" id="fi_active_off" value="0"/>{l s='No' mod='psmgen'}</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Validate header' mod='psmgen'}
                    </label>

                    <div class="col-lg-9 ">
                        <div class="radio radioCheck">
                            <label><input type="radio" name="function_header" id="fh_active_on" value="1" checked="checked"/>{l s='Yes' mod='psmgen'}</label>
                        </div>
                        <div class="radio radioCheck">
                            <label><input type="radio" name="function_header" id="fh_active_off" value="0"/>{l s='No' mod='psmgen'}</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Validate $_POST' mod='psmgen'}
                    </label>
                    <div class="col-lg-9 ">
                        <div class="radio radioCheck">
                            <label><input type="radio" name="module_post" id="mp_active_on" value="1" checked="checked"/>{l s='Yes' mod='psmgen'}</label>
                        </div>
                        <div class="radio radioCheck">
                            <label><input type="radio" name="module_post" id="mp_active_off" value="0"/>{l s='No' mod='psmgen'}</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Validate file_get_content' mod='psmgen'}
                    </label>
                    <div class="col-lg-9 ">
                        <div class="radio radioCheck">
                            <label><input type="radio" name="module_fgc" id="fgc_active_on" value="1" checked="checked"/>{l s='Yes' mod='psmgen'}</label>
                        </div>
                        <div class="radio radioCheck">
                            <label><input type="radio" name="module_fgc" id="fgc_active_off" value="0"/>{l s='No' mod='psmgen'}</label>
                        </div>
                    </div>

                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {l s='Validate $_SESSION' mod='psmgen'}
                    </label>
                    <div class="col-lg-9 ">
                        <div class="radio radioCheck">
                            <label><input type="radio" name="module_session" id="fs_active_on" value="1" checked="checked"/>{l s='Yes' mod='psmgen'}</label>
                        </div>
                        <div class="radio radioCheck">
                            <label><input type="radio" name="module_session" id="fs_active_off" value="0"/>{l s='No' mod='psmgen'}</label>
                        </div>
                    </div>
                </div>

                <button type="submit" value="1"	id="configuration_form_submit_btn" name="btnSubmit" class="button">
                    <i class="process-icon-save"></i> Guardar
                </button>
                {*<input type="submit" name="btnSubmit" value="Enviar" />*}

                </form>

                {*<form id="configuration_form" class="defaultForm form-horizontal" method="post" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="submit" value="1" />
                
                <div class="panel" id="fieldset_0">
                
                <div class="panel-heading">
                Settings
                </div>
                
                <div class="form-wrapper">
                
                <div class="form-group">
                
                <label class="control-label col-lg-3 required">
                Import zip module
                </label>
                
                <div class="col-lg-9 ">
                <div class="form-group">
                <div class="col-sm-6">
                
                <input type="file" name="nombre_archivo_cliente" /><br />
                <input id="import" type="file" name="import" class="hide" style="display:none" accept=".zip"/>
                <div class="dummyfile input-group">
                <span class="input-group-addon"><i class="icon-file"></i></span>
                <input id="import-name" type="text" name="filename" readonly />
                <span class="input-group-btn">
                <button id="import-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
                <i class="icon-folder-open"></i> Añadir archivo				
                </button>
                </span>
                </div>
                </div>
                </div>*}
                {*<script type="text/javascript">
                
                $(document).ready(function () {
                $('#import-selectbutton').click(function (e) {
                $('#import').trigger('click');
                });
                
                $('#import-name').click(function (e) {
                $('#import').trigger('click');
                });
                
                $('#import-name').on('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
                });
                
                $('#import-name').on('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
                });
                
                $('#import-name').on('drop', function (e) {
                e.preventDefault();
                var files = e.originalEvent.dataTransfer.files;
                $('#import')[0].files = files;
                $(this).val(files[0].name);
                });
                
                $('#import').change(function (e) {
                if ($(this)[0].files !== undefined)
                {
                var files = $(this)[0].files;
                var name = '';
                
                $.each(files, function (index, value) {
                name += value.name + ', ';
                });
                
                $('#import-name').val(name.slice(0, -2));
                }
                else // Internet Explorer 9 Compatibility
                {
                var name = $(this).val().split(/[\\/]/);
                $('#import-name').val(name[name.length - 1]);
                }
                });
                
                if (typeof import_max_files !== 'undefined')
                {
                $('#import').closest('form').on('submit', function (e) {
                if ($('#import')[0].files.length > import_max_files) {
                e.preventDefault();
                alert('You can upload a maximum of  files');
                }
                });
                }
                });
                </script>*}
                {*             </div>
                </div>
                </div><!-- /.form-wrapper -->
                
                <div class="panel-footer">
                <button type="submit" value="1"	id="configuration_form_submit_btn" name="btnSubmit" class="button">
                <i class="process-icon-save"></i> Guardar
                </button>
                </div>
                </div>
                </form>*}