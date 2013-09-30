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
                            <td class="left">SKU</td>
                            <td class="left">Name</td>
                        </tr>
                    </thead>
                    <tr>
                        <td class="left" colspan="2">
                            <img src="<?php echo HTTPS_SERVER; ?>view/image/loading.gif" /> Loading
                        </td>
                    </tr>
                    <tbody style="display:none;" id="show_linked_items">

                        <?php foreach($available_items as $id => $item) { ?>
                            <tr>
                                <td class="left"><?php echo $item['sku']; ?></td>
                                <td class="left"><?php echo $item['name']; ?></td>
                            </tr>
                        <?php } ?>

                    </tbody>
                </table>


                <div class="pagination"><?php echo $pagination; ?></div>

            <?php }else{ ?>
                <div class="warning"><?php echo $lang_error_validation; ?></div>
            <?php } ?>

        </div>
    </div>
</div>

<?php echo $footer; ?>