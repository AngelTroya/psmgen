{**   
* Module PSMGen
*  @author    Angel Maria de Troya de la Vega <angelmaria87@gmail.com>
*  @copyright 2014 
*  @license   CopyRight
**}

{*
{$articleTitle|escape}
{$articleTitle|escape:"html"}     escapes  & " ' < > 
{$articleTitle|escape:"htmlall"}  escapes ALL html entities
{$articleTitle|escape:"url"}
{$articleTitle|escape:"quotes"}
*}
{*<script type="text/css">
@import url({$base_dir}modules/psmgen/js/psmgen.js);
</script>*}

<h1> {l s='PrestaShop Module Generator' mod='psmgen'} </h1>
{*{$form1}*}

{*action="index.php?controller=AdminPSMGen&amp;configure=psmgen&amp;token=132025fe4e708a0d162964c0b3ce3140"*}
<form action = "{$controller_link|escape:"htmlall"}&token={$smarty.get.token|escape:"htmlall"}" id="configuration_form" class="defaultForm form-horizontal psmgen"  method="post" enctype="multipart/form-data" {*novalidate*}>
    <input type="hidden" name="submitpsmgen" value="1" />

    <div class="panel" id="fieldset_0">

        <div class="form-wrapper">

            <div class="form-group">

                <label class="control-label col-lg-3 required">
                    {l s='Project name' mod='psmgen'}
                </label>



                <div class="col-lg-9 ">

                    <input type="text"
                           name="module_project_name"
                           id="module_project_name"
                           value=""
                           class=""
                           size="30"
                           required/>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3 required">
                    {l s='Author' mod='psmgen'}
                </label>

                <div class="col-lg-9 ">
                    <input type="text"
                           name="module_author"
                           id="module_author"
                           value=""
                           class=""
                           size="30"
                           required/>
                </div>
            </div>

            <div class="form-group">

                <label class="control-label col-lg-3 required">
                    {l s='Version' mod='psmgen'}
                </label>

                <div class="col-lg-9 ">

                    <input type="text"
                           name="module_version"
                           id="module_version"
                           value=""
                           class=""
                           size="30"	
                           required />
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3 required">
                    {l s='Description' mod='psmgen'}
                </label>

                <div class="col-lg-9 ">
                    <textarea name="module_description" id="module_description"  rows="5" class="rte autoload_rte"></textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3 required">
                    {l s='Tab' mod='psmgen'}
                </label>
                <div class="col-lg-9 ">
                    <select name="module_tab"
                            class=" fixed-width-xl"
                            id="module_tab">
                        <option value="administration"
                                >Administration</option>

                        <option value="advertising_marketing"
                                >Advertising and Marketing</option>

                        <option value="analytics_stats"
                                >Analytics and Stats</option>

                        <option value="billing_invoicing"
                                >Taxes &amp; Invoicing</option>

                        <option value="checkout"
                                >Checkout</option>

                        <option value="content_management"
                                >Content Management</option>

                        <option value="dashboard"
                                >Dashboard</option>

                        <option value="emailing"
                                >Emailing</option>

                        <option value="export"
                                >Exportar</option>

                        <option value="front_office_features"
                                >Front Office Features</option>

                        <option value="i18n_localization"
                                >Internationalization and Localization</option>

                        <option value="market_place"
                                >Marketplace</option>

                        <option value="merchandizing"
                                >Merchandising</option>

                        <option value="migration_tools"
                                >Migration Tools</option>

                        <option value="mobile"
                                >Mobile</option>

                        <option value="others"
                                >Other Modules</option>

                        <option value="payments_gateways"
                                >Payments and Gateways</option>

                        <option value="payment_security"
                                >Site certification &amp; Fraud prevention</option>

                        <option value="pricing_promotion"
                                >Pricing and Promotion</option>

                        <option value="quick_bulk_update"
                                >Quick / Bulk update</option>

                        <option value="seo"
                                >SEO</option>

                        <option value="shipping_logistics"
                                >Shipping and Logistics</option>

                        <option value="slideshows"
                                >Slideshows</option>

                        <option value="smart_shopping"
                                >Comparison site &amp; Feed management</option>

                        <option value="social_networks"
                                >Social Networks</option>
                    </select>
                </div>
            </div>


            <div class="form-group">

                <label class="control-label col-lg-3">
                    {l s='Admin Module' mod='psmgen'}
                </label>

                <div class="col-lg-9 ">

                    <div class="radio ">
                        <label><input type="radio" name="module_adminModule" id="am_active_on" value="1"/>{l s='Yes' mod='psmgen'}</label>
                    </div>
                    <div class="radio ">
                        <label><input type="radio" name="module_adminModule" id="am_active_off" value="0" checked="checked"/>{l s='No' mod='psmgen'}</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='Need Instance' mod='psmgen'}
                </label>
                <div class="col-lg-9 ">
                    <div class="radio radioCheck">
                        <label><input type="radio" name="module_needInstance" id="ni_active_on" value="1"/>{l s='Yes' mod='psmgen'}</label>
                    </div>
                    <div class="radio radioCheck">
                        <label><input type="radio" name="module_needInstance" id="ni_active_off" value="0" checked="checked"/>{l s='No' mod='psmgen'}</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='Configuration page' mod='psmgen'}
                </label>
                <div class="col-lg-9 ">
                    <div class="radio radioCheck">
                        <label><input type="radio" name="module_configpage" id="cp_active_on" value="1"/>{l s='Yes' mod='psmgen'}</label>
                    </div>
                    <div class="radio radioCheck">
                        <label><input type="radio" name="module_configpage" id="cp_active_off" value="0" checked="checked"/>{l s='No' mod='psmgen'}</label>
                    </div>
                </div>
            </div>

            <div>
                <div class="col-lg-9 col-lg-offset-3">
                    {foreach from=$sql item=sql_item}
                        <div class="checkbox">
                            <label  for="{$sql_item['label']}" style="width: 360px; text-align:left"> <input type="checkbox" name="{$sql_item['id']}" id="{$sql_item['id']}" />{$sql_item['label']} </label>
                        </div>
                    {/foreach}   
                </div>
            </div>
                
            
        </div><!-- /.form-wrapper -->
        <div class="panel-footer">
                <button type="submit" value="1"	id="configuration_form_submit_btn" name="btnSubmit" class="button">
                    <i class="process-icon-save"></i> {l s='Save' mod='psmgen'}
                </button>
            </div>

    </div>
</form>
{*{$smarty.GET.token}*}
<!-- /MODULE Home Featured Products -->
