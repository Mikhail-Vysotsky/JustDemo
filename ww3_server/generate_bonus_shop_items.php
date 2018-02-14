<?php
/**
 * Created by PhpStorm.
 * User: vysotsky
 * Date: 20.06.16
 * Time: 14:18
 */
if (!RS_Config::get()->selenium_enable ) {
    die();
}

require_once dirname(__FILE__) .'/../../inc/shell.inc.php';

m_ln("START", 1);

// EUR
$clean_values = ["10", "20", "50", "100",];
$dirty_values = ["10", "20", "50", "100", "500", "1000"];

LB_BonusSystem_Shop_Product_Storage::get()->delete_all("1=1");

$global_bonus_system = LB_BonusSystem_Service::get()->get_bonus_system();

// just one image using
$icon_file = __DIR__.'/bonus_shop_item_icon.jpg';


foreach (Currency_Service::get()->active_list as $item)
{
    foreach ($clean_values as $v)
    {
        $card_value = Currency_Service::get()->convert_value_from_eur_to_currency($v, $item->name);
        $card_value = _round($card_value);

        $price = $global_bonus_system->get_price_of_clean_card($card_value, $item->name);
        $price = _round($price);

        $product = new LB_BonusSystem_Shop_Product([
            "icon" => prepare_icon($icon_file),
            "card_currency" => $item->name,
            "card_value"    => $card_value,
            "price"         => $price,
            "product_type"  => LB_BonusSystem_Shop_Product::PRODUCT_TYPE_CLEAN_MONEY
        ]);
        LB_BonusSystem_Shop_Product_Storage::get()->save_with_revision($product, LB_Revisions_Data::create_for_script(LB_Revisions_Info::ADAPTER_ADMIN_EDIT_PRODUCT_CLEAN_CARD));

    }

    foreach ($dirty_values as $v)
    {
        $card_value = Currency_Service::get()->convert_value_from_eur_to_currency($v, $item->name);
        $card_value = _round($card_value);

        $price = $global_bonus_system->get_price_of_dirty_card($card_value, $item->name);
        $price = _round($price);

        $price_to_clean = $global_bonus_system->get_price_to_clean_dirty_card($card_value, $item->name);
        $price_to_clean = _round($price_to_clean);


        $product = new LB_BonusSystem_Shop_Product([
            "icon" => prepare_icon($icon_file),
            "card_currency"    => $item->name,
            "card_value"       => $card_value,
            "price"            => $price,
            "card_clean_price" => $price_to_clean,
            "card_expiration_length" => 30,
            "product_type"     => LB_BonusSystem_Shop_Product::PRODUCT_TYPE_DIRTY_MONEY
        ]);

        LB_BonusSystem_Shop_Product_Storage::get()->save_with_revision($product, LB_Revisions_Data::create_for_script(LB_Revisions_Info::ADAPTER_ADMIN_EDIT_PRODUCT_DIRTY_CARD));
    }

}

function _round($val)
{
    return ceil($val);
}

function prepare_icon($_icon_file)
{
    $name = uniqid().'.'.pathinfo($_icon_file, PATHINFO_EXTENSION);
    $dest_file = __DIR__ . '/../../../usr/image_temp/' . $name;
    $www_path = '/usr/image_temp/' . $name;

    copy($_icon_file, $dest_file);

    $db_file = FileRepository_Service::get()->save_image_to_db(file_get_contents($dest_file), $www_path, 1);

    return $www_path;
}

m_ln("END", 1);

