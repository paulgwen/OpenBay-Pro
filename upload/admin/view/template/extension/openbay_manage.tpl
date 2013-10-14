<?php echo $header; ?>
<div id="content">
    <div class="breadcrumb">
<?php 
        foreach ($breadcrumbs as $breadcrumb) {
            echo $breadcrumb['separator'].'<a href="'.$breadcrumb['href'].'">'.$breadcrumb['text'].'</a>';
        } 
?>
    </div>

    <div class="box mBottom130">
        <div class="left"></div>
        <div class="right"></div>
        <div class="heading">
            <h1><?php echo $lang_text_manager; ?></h1>
            <div class="buttons"><a onclick="validateForm(); return false;" class="button"><span><?php echo $lang_btn_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $lang_btn_cancel; ?></span></a></div>
        </div>
        <div class="content">
            <div id="tabs" class="htabs">
                <a href="#tab-updates"><?php echo $lang_btn_update; ?></a>
                <a href="#tab-settings"><?php echo $lang_btn_settings; ?></a>
                <a href="#tab-help"><?php echo $lang_tab_help; ?></a>
            </div>

            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                <div id="tab-updates">
                    <p><?php echo $lang_patch_notes1; ?> <a href="http://shop.openbaypro.com/index.php?route=information/information/changelog" title="OpenBay Pro change log" target="_BLANK"><?php echo $lang_patch_notes2; ?></a></p>

                    <p><?php echo $lang_patch_notes3; ?></p>

                    <p><a onclick="runPatch();" class="button" id="runPatch"><span><?php echo $lang_run_patch; ?></span></a><img src="<?php echo HTTPS_SERVER; ?>view/image/loading.gif" id="imageRunPatch" class="displayNone" /></p>
                </div>

                <div id="tab-settings">
                    <table class="form">
                        <tr>
                            <td ><?php echo $lang_language; ?></td>
                            <td>
                                <select name="openbay_language">
                                    <?php foreach($languages as $key => $language){ ?>
                                        <option value="<?php echo $key; ?>" <?php if($key == $openbay_language){ echo'selected="selected"'; } ?>><?php echo $language; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td valign="middle"><label for=""><?php echo $lang_clearfaq; ?></td>
                            <td><a onclick="clearFaq();" class="button" id="clearFaq"><span><?php echo $lang_clearfaqbtn; ?></span></a><img src="<?php echo HTTPS_SERVER; ?>view/image/loading.gif" id="imageClearFaq" class="displayNone" /></td>
                        </tr>
                    </table>
                </div>

                <div id="tab-help">
                    <h2><?php echo $lang_help_title; ?></h2>
                    <table class="form">
                        <tr>
                            <td class="p10"><?php echo $lang_help_support_title; ?></td>
                            <td class="p10"><?php echo $lang_help_support_description; ?></td>
                        </tr>
                        <tr>
                            <td class="p10"><?php echo $lang_help_template_title; ?></td>
                            <td class="p10"><?php echo $lang_help_template_description; ?></td>
                        </tr>
                        <tr>
                            <td class="p10"><?php echo $lang_help_guide; ?></td>
                            <td class="p10"><?php echo $lang_help_guide_description; ?></td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">

    var token = "<?php echo $_GET['token']; ?>";

    function runPatch(){
        $.ajax({
            url: 'index.php?route=extension/openbay/runPatch&token='+token,
            type: 'post',
            dataType: 'json',
            beforeSend: function(){
                $('#runPatch').hide();
                $('#imageRunPatch').show();
            },
            success: function() {
                alert('<?php echo $lang_patch_applied; ?>');
                $('#runPatch').show();
                $('#imageRunPatch').hide();
            }
        });
    }
    
    function validateForm(){
        $('#form').submit();
    }

    function clearFaq(){
        $.ajax({
            url: 'index.php?route=extension/openbay/faqClear&token='+token,
            beforeSend: function(){
                $('#clearFaq').hide();
                $('#imageClearFaq').show();
            },
            type: 'post',
            dataType: 'json',
            success: function(json) {
                $('#clearFaq').show(); $('#imageClearFaq').hide();
            },
            failure: function(){
                $('#imageClearFaq').hide();
                $('#clearFaq').show();
            },
            error: function(){
                $('#imageClearFaq').hide();
                $('#clearFaq').show();
            }
        });
    }

</script>

<script type="text/javascript"><!--
$('#tabs a').tabs();
//--></script> 
