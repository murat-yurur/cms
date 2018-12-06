<div class="row">
    <div class="col-md-12">
        <h4 class="m-b-lg">
            ePosta Ayar Seti Listesi
            <a class="btn btn-outline btn-info btn-sm pull-right"
               href="<?php echo base_url("emailsettings/new_form"); ?>">
                <i class="fa fa-plus"></i> Yeni Ekle
            </a>
        </h4>
    </div>
    <div class="col-md-12">
        <div class="widget p-lg">
            <?php if (empty($items)) { ?>
                <div class="alert alert-warning text-center" style="padding: 8px; margin-bottom: 0px; s">
                    <p style="font-size: larger">Henüz hiçbir kullanıcı eklenmemiş. Eklemek için
                        <a href="<?php echo base_url("emailsettings/new_form"); ?>">
                            tıklayın
                        </a>...
                    </p>
                </div>
            <?php } else { ?>
                <table id="datatable-responsive"
                       class="table table-striped table-hover table-bordered content-container">
                    <thead>
                    <th class="w50">#id</th>
                    <th>Gönderici Adı</th>
                    <th>ePosta Sunucu (Host)</th>
                    <th>Protokol</th>
                    <th>Port Numarası</th>
                    <th>Sunucu Kullanıcı Adı</th>
                    <th>Kimden</th>
                    <th>Kime</th>
                    <th class="w75">Durumu</th>
                    <th class="w150">İşlem</th>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item) { ?>
                        <tr>
                            <td class="text-center"><?php echo $item->id; ?></td>
                            <td class="text-center"><?php echo $item->user_name; ?></td>
                            <td class="text-center"><?php echo $item->host ?></td>
                            <td class="text-center"><?php echo $item->protocol; ?></td>
                            <td class="text-center"><?php echo $item->port; ?></td>
                            <td class="text-center"><?php echo $item->user; ?></td>
                            <td class="text-center"><?php echo $item->from; ?></td>
                            <td class="text-center"><?php echo $item->to; ?></td>
                            <td class="text-center">
                                <input
                                        data-url="<?php echo base_url("emailsettings/isActiveSetter/$item->id"); ?>"
                                        class="isActive"
                                        type="checkbox"
                                        data-switchery
                                        data-color="#188ae2"
                                    <?php echo ($item->isActive) ? "checked" : "" ?>
                                />
                            </td>
                            <td class="text-center">
                                <button
                                        data-url="<?php echo base_url("emailsettings/delete/$item->id"); ?>"
                                        type="button"
                                        class="btn btn-danger btn-sm btn-outline remove-btn"
                                >
                                    <i class="fa fa-trash-o"></i>
                                    Sil
                                </button>
                                <a href="<?php echo base_url("emailsettings/update_form/$item->id"); ?>">
                                    <button type="button" class="btn btn-primary btn-sm btn-outline">
                                        <i class="fa fa-pencil-square-o"></i>
                                        Düzenle
                                    </button>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div><!-- .widget -->
    </div><!-- END column -->
</div>