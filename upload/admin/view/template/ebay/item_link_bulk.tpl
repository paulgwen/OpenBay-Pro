<?php echo $header; ?>

<div id="content">

    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

    <div class="box mBottom130">

        <div class="heading">
            <h1><?php echo $lang_heading; ?></h1>
            <div class="buttons">
                <a href="<?php echo $return; ?>" class="button"><span><?php echo $lang_btn_return; ?></span></a>
            </div>
        </div>

        <div class="content">

            <?php if($validation == true) { ?>

            <table class="list" cellpadding="2">
                <thead>
                <tr>
                    <td class="center" width="40"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></td>
                    <td class="left">SKU</td>
                    <td class="left">Product Name</td>
                    <td class="left">Matched eBay listing</td>
                    <td class="left">Status</td>
                </tr>
                </thead>
                <tr>
                    <td class="left" colspan="5" id="show_linked_items_loading">
                        <img src="<?php echo HTTPS_SERVER; ?>view/image/loading.gif" /> Loading
                    </td>
                </tr>
                <tbody style="display:none;" id="show_linked_items">

                <?php $row_id = 1; ?>
                <?php foreach($available_items as $id => $item) { ?>
                    <input type="hidden" class="sku_row_id" value="<?php echo $item['sku']; ?>" id="sku_row_<?php echo $row_id; ?>" />
                    <input type="hidden" value="<?php echo $item['product_id']; ?>" id="sku_row_<?php echo $row_id; ?>_product_id" />
                    <input type="hidden" value="<?php echo $item['quantity']; ?>" id="sku_row_<?php echo $row_id; ?>_qty" />

                    <tr id="sku_row_<?php echo $row_id; ?>_tr" class="no_match">
                        <td class="center" id="sku_row_<?php echo $row_id; ?>_checkbox"></td>
                        <td class="left"><?php echo $item['sku']; ?></td>
                        <td class="left"><?php echo $item['name']; ?></td>
                        <td class="left link_choice" id="sku_row_<?php echo $row_id; ?>_options"></td>
                        <td class="left" id="sku_row_<?php echo $row_id; ?>_status">Unlinked</td>
                    </tr>

                    <?php $row_id++; ?>
                <?php } ?>

                </tbody>
            </table>

            <div class="buttons">
                <a style="display:none;" id="btn_link_action" onclick="linkItems();" class="button displayNone"><span>Link selected items</span></a>
                <a style="display:none;" id="btn_toggle_match" onclick="$('.no_match').toggle();" class="button displayNone"><span>Toggle no matches</span></a>
            </div>

            <br />

            <input type="hidden" name="item_page" id="item_page" value="<?php echo $item_page; ?>" />

            <div class="pagination"><?php echo $pagination; ?></div>

            <?php }else{ ?>
                <div class="warning"><?php echo $lang_error_validation; ?></div>
            <?php } ?>

        </div>
    </div>
</div>

<script type="text/javascript"><!--
    function bulkLinkingRequest(){
        var item_page = $('#item_page').val();
        var row_selector = '';

        $.ajax({
            url: 'index.php?route=openbay/openbay/bulkLinkingRequest&token=<?php echo $token; ?>&item_page='+item_page,
            type: 'post',
            dataType: 'json',
            beforeSend: function(){ },
            success: function(json) {
                $.each(json.results, function(key, val){
                    row_selector = $('.sku_row_id[value='+val.sku+']').attr('id');

                    $('#'+row_selector+'_options').append('<p><input type="radio" name="'+row_selector+'_choice" value="'+val.item_id+'" /><input type="hidden" id="'+row_selector+'_qty_'+val.item_id+'" value="'+val.stock+'" /> '+val.item_id+' '+val.title+'</p>');

                    $('#'+row_selector+'_checkbox').html('<input class="link_selected" type="checkbox" name="link_selected[]" value="'+row_selector+'" />');

                    $('#'+row_selector+'_tr').removeClass('no_match');
                });

                $('.link_choice').each(function(){
                    $('input[type=radio]:first', this).attr('checked', true);
                });

                if($('.no_match').length > 0){
                    $('#btn_toggle_match').show();
                }

                if(json.matches > 0){
                    $('#btn_link_action').show();
                }

                $('#show_linked_items_loading').hide();
                $('#show_linked_items').show();
            },
            failure: function(){

            },
            error: function(){

            }
        });
    }

    function linkItems(){
        if($('.link_selected:checked').length > 0){
            var sku_row_id = '';
            var product_id = '';
            var qty = '';
            var ebay_id = '';
            var ebay_qty = '';

            $('#btn_link_action').hide();

            $('.link_selected:checked').each(function() {
                sku_row_id = $(this).val();
                product_id = $('#'+sku_row_id+'_product_id').val();
                qty = $('#'+sku_row_id+'_qty').val();
                ebay_id = $('input:radio[name="'+sku_row_id+'_choice"]:checked').val();
                ebay_qty = $('#'+sku_row_id+'_qty_'+ebay_id+'').val();

                $('#'+sku_row_id+'_status').empty().text('Pending..');

                saveListingLink(product_id, ebay_id, qty, ebay_qty, sku_row_id);
            });
        }else{
            alert('You must select some items to link');
        }
    }

    function saveListingLink(product_id, ebay_id, qty, ebay_qty, sku_row_id){

        $.ajax({
            url: 'index.php?route=openbay/openbay/saveItemLink&token=<?php echo $token; ?>&pid='+product_id+'&itemId='+ebay_id+'&qty='+qty+'&ebayqty='+ebay_qty+'&variants=',
            type: 'post',
            dataType: 'json',
            beforeSend: function(){
                $('#'+sku_row_id+'_status').empty().html('<img src="<?php echo HTTPS_SERVER; ?>view/image/loading.gif" />');
            },
            success: function(json) {
                $('#'+sku_row_id+'_status').empty().html('<img src="<?php echo HTTPS_SERVER; ?>view/image/success.png" />');
                $('#'+sku_row_id+'_checkbox').empty();
            }
        });
    }
//--></script>

<?php if($validation == true) { ?>
<script type="text/javascript"><!--
    $(document).ready(function() {
        bulkLinkingRequest();
    });
    //--></script>
<?php } ?>

<?php echo $footer; ?>