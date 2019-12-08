<?php

header("Content-type: application/json; charset=utf-8");
	// $files = glob('packing_v2/xml_zipping/*'); // get all file names
	$files = array('MT_SBX108_20181102_tm_sinta_56a908_SI.jpg',
'MT_NJK192_20181103_tm_fmckediri2_c3d7f0_SI.jpg',
'MT_SMN021_20181103_tm_mei_71bf20_SI.jpg',
'MT_SMN614_20181105_tm_sugimin1_eb61b_SI.jpg',
'MT_SBY664_20181105_tm_juki_32bb41_SI.jpg',
'MT_SBY664_20181105_tm_juki_32bb41_SI.jpg',
'MT_COF251_20181105_tm_windi_a498a5_SI.jpg',
'MT_SMG715_20181105_tm_fery_ak_4ae678_SI.jpg',
'MT_SBX314_20181105_tm_toyib_41cd6c_SI.jpg',
'MT_UNR018_20181106_tm_agus_pra_a3c1e1_SI.jpg',
'MT_PAT663_20181106_tm_dian_0762ed_SI.jpg',
'MT_PAT663_20181106_tm_dian_0762ed_SI.jpg',
'MT_WNG651_20181107_tm_Andy_5174a5_SI.jpg',
'MT_UNR024_20181108_tm_agus_pra_2b4ea5_SI.jpg',
'MT_CLP136_20181108_tm_sukartono_2f2ab6_SI.jpg',
'MT_SBY564_20181109_tm_yosi_10f093_SI.jpg',
'MT_SBY501_20181109_tm_maksum_64320d_SI.jpg',
'MT_skh020_20181109_tm_Fitri_83020d_SI.jpg',
'MT_skh116_20181109_tm_Fitri_d03725_SI.jpg',
'MT_BKN161_20181110_tm_ketapang_15bfdc_SI.jpg',
'MT_BTL697_20181112_tm_ekop_329c39_SI.jpg',
'MT_BTL697_20181112_tm_ekop_329c39_SI.jpg',
'MT_tag011_20181112_tm_eko_spm_a68fe6_SI.jpg',
'MT_tag045_20181112_tm_eko_spm_4ad9c4_SI.jpg',
'MT_NJK194_20181112_tm_fmcnjk2_1ecc35_SI.jpg',
'MT_SRA018_20181112_tm_agus_sup_9362fd_SI.jpg',
'MT_PSN105_20181113_tm_ekojohanwahyudi_xer_5617f2_SI.jpg',
'MT_GRO025_20181113_mbp_budi_set_5484e9_SI.jpg',
'MT_SMN699_20181114_tm_antonsar_ec88b0_SI.jpg',
'MT_SMN699_20181114_tm_antonsar_ec88b0_SI.jpg',
'MT_GSK008_20181114_tm_teguh_f59835_SI.jpg',
'MT_NJK091_20181114_tm_fmckediri2_4bbb2d_SI.jpg',
'MT_SBY158_20181115_tm_lasmin_db2272_SI.jpg',
'MT_SBX005_20181115_tm_yosi_958393_SI.jpg',
'MT_SBX043_20181115_tm_yosi_32836e_SI.jpg',
'MT_GSK018_20181115_tm_putra_5e832d_SI.jpg',
'MT_tag114_20181115_tm_mubaid_spm_feb7e8_SI.jpg',
'MT_tag114_20181115_tm_mubaid_spm_feb7e8_SI.jpg',
'MT_MGL089_20181115_tm_dimas_1b7273_SI.jpg',
'MT_MGL089_20181115_tm_wahyu_1b7273_SI.jpg',
'MT_MGL089_20181115_tm_wahyu_1b7273_SI.jpg',
'MT_GRO075_20181116_mbp_budi_set_fc0720_SI.jpg',
'MT_SBY005_20181116_tm_yosi_958393_SI.jpg',
'MT_KED167_20181116_tm_maintkediri1_74deb6_SI.jpg',
'MT_MKD008_20181116_tm_wahyu_4d776e_SI.jpg',
'MT_byl634_20181116_tm_Heri_0ade1e_SI.jpg',
'MT_SMG837_20181117_tm_fery_ak_9ed43b_SI.jpg',
'MT_GSK166_20181117_tm_teguh_27b766_SI.jpg',
'MT_MLG788_20181117_tm_hadi_727b90_SI.jpg',
'MT_SKH743_20181119_tm_Bayu_2f12a0_SI.jpg',
'MT_SBY147_20181119_tm_yosi_1f0d46_SI.jpg',
'MT_sby290_20181119_tm_sinta_5437a5_SI.jpg',
'MT_COF262_20181119_tm_vera_ab9854_SI.jpg',
'MT_KED190_20181119_tm_maintkediri1_696333_SI.jpg',
'MT_SKH691_20181119_tm_Bayu_8bdc0a_SI.jpg',
'MT_SBZ351_20181121_suJTM003_264398_SI.jpg');


	$response['files'] = $files;	

	foreach($files as $file){ 
		$site_id = explode("_",$file);
		$site_id = $site_id[1];
		$lokasi_dir = "images_backup/".$site_id."/2018/11/".$file; 
		$target_dir = "images/".$file;

		$status_copy = copy($lokasi_dir, $target_dir);

		$response['file'] = $file;
		$response['site_id'] = $site_id;
		$response['lokasi_dir'] = $lokasi_dir;
		$response['target_dir'] = $target_dir;
		$response['status_copy'] = $status_copy;
	

	}	
	
		die(json_encode($response));
	


?>