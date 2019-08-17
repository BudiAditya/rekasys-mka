<!DOCTYPE HTML>
<html>
<?php
/** @var $month int */ /** @var $year int */ /** @var $docTypes DocType[] */ /** @var $docIds int[] */ /** @var $vocTypes VoucherType[] */
/** @var $report ReaderBase */ /** @var $output string */ /** @var $company Company */ /** @var $orientation string */ /** @var $status int */ /** @var $monthNames array */
/** @var $projectId int */ /** @var $projectList Project[] */
?>
<head>
	<title>Rekasys - Report Rekap Jurnal</title>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
	<link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>
	<style type="text/css">
		#list {
			margin: 0;
			padding: 0;
		}
		#list li {
			display: inline-block;
			padding: 0 2px;
		}
		#list li label {
			position: relative;
			top: 1px;
			display: inline-block;
			width: 150px;
			overflow: hidden;
		}
		.nowrap { white-space: nowrap; }
	</style>

	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
	<script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
	<script type="text/javascript">
		function CheckAll(type) {
			$("#list").find(":checkbox").each(function(idx, ele) {
				var voucher = $(ele).attr("voucher");
				if (voucher == type) {
					ele.checked = "checked";
				} else {
					ele.checked = "";
				}
			});
		}
	</script>
</head>

<body>

<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
	<div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
	<div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />

<fieldset>
	<legend><span class="bold">Report Rekap Jurnal</span></legend>

	<form action="<?php print($helper->site_url("accounting.report/recap")); ?>" method="GET">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto; width: 80%;">
			<tr>
				<td class="nowrap right"><label for="Month">Periode : </label></td>
				<td>
					<select id="Month" name="month">
						<?php
						foreach ($monthNames as $idx => $name) {
							if ($idx == $month) {
								printf('<option value="%d" selected="selected">%s</option>', $idx, $name);
							} else {
								printf('<option value="%d">%s</option>', $idx, $name);
							}
						}
						?>
					</select>
					<label for="Year">Tahun : </label>
					<select id="Year" name="year">
						<?php
						for ($i = date("Y"); $i >= 2010; $i--) {
							if ($i == $year) {
								printf('<option value="%d" selected="selected">%s</option>', $i, $i);
							} else {
								printf('<option value="%d">%s</option>', $i, $i);
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="nowrap right" valign="top"><label style="margin-top: 2px; display: inline-block;">Jenis Dokumen :</label></td>
				<td>
					<ul id="list">
						<?php
						$buff = array();
						foreach ($docTypes as $docType) {
							if (in_array($docType->Id, $docIds)) {
								$checkbox = sprintf('<input type="checkbox" id="cb_%d" name="docType[]" value="%d" voucher="%s" checked="checked" />', $docType->Id, $docType->Id, strtoupper($docType->VoucherCd));
								$buff[] = strtoupper($docType->DocCode);
							} else {
								$checkbox = sprintf('<input type="checkbox" id="cb_%d" name="docType[]" value="%d" voucher="%s" />', $docType->Id, $docType->Id, strtoupper($docType->VoucherCd));
							}
							$label = sprintf('<label for="cb_%d" class="nowrap">%s - %s</label>', $docType->Id, $docType->DocCode, $docType->Description);
							printf("<li>%s %s</li>", $checkbox, $label);
						}
						?>
					</ul>
				</td>
			</tr>
			<tr>
				<td class="nowrap right">Centang Semua :</td>
				<td>
					<?php
					foreach ($vocTypes as $vocType) {
						printf('<button type="button" onclick="CheckAll(\'%s\')">%s</button>', strtoupper($vocType->VoucherCd), $vocType->VoucherCd);
					}
					?>
				</td>
			</tr>
            <tr>
                <td class="right"><label for="Project">Pilih Proyek : </label></td>
                <td>
                    <select id="Project" name="projectId">
                        <option value="0">-- Not Filtered --</option>
                        <?php
                        $selectedProject = null;
                        foreach ($projectList as $project) {
                            if($project->Id == $projectId){
                                $selectedProject = $project;
                                printf('<option value="%d" selected="selected">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                            }else{
                                printf('<option value="%d">%s - %s</option>', $project->Id, $project->ProjectCd, $project->ProjectName);
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
			<tr>
				<td class="nowrap right"><label for="Status">Status Voucher :</label></td>
				<td>
					<select id="Status" name="status">
						<option value="">SEMUA STATUS</option>
						<option value="1" <?php print($status == 1 ? 'selected="selected"' : ''); ?>>BELUM APPROVED</option>
						<option value="2" <?php print($status == 2 ? 'selected="selected"' : ''); ?>>SUDAH APPROVED</option>
						<option value="3" <?php print($status == 3 ? 'selected="selected"' : ''); ?>>VERIFIED</option>
						<option value="4" <?php print($status == 4 ? 'selected="selected"' : ''); ?>>POSTED</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="nowrap right"><label for="Output">Output : </label></td>
				<td>
					<select id="Output" name="output">
						<option value="web" <?php print($output == "web" ? 'selected="selected"' : '') ?>>Web Browser</option>
						<option value="pdf" <?php print($output == "pdf" ? 'selected="selected"' : '') ?>>PDF</option>
						<option value="xls" <?php print($output == "xls" ? 'selected="selected"' : '') ?>>Excel</option>
					</select>
					<label for="Orientation"> posisi cetak : </label>
					<select id="Orientation" name="orientation">
						<option value="p">Portrait</option>
						<option value="l">Landscape</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button type="submit">Generate</button>
				</td>
			</tr>
		</table>
	</form>
</fieldset>


<!-- REGION: LAPORAN -->
<?php
if ($report != null) {
	switch ($status) {
		case 1:
			$subTitle = "REKAP JURNAL: " . implode(", ", $buff) . " status: BELUM APPROVED";
			break;
		case 2:
			$subTitle = "REKAP JURNAL: " . implode(", ", $buff) . " status: SUDAH APPROVED";
			break;
		case 3:
			$subTitle = "REKAP JURNAL: " . implode(", ", $buff) . " status: VERIFIED";
			break;
		case 4:
			$subTitle = "REKAP JURNAL: " . implode(", ", $buff) . " status: POSTED";
			break;
		default:
			$subTitle = "REKAP JURNAL: " . implode(", ", $buff) . " status: SEMUA";
			break;
	}
?>
<br />
<div class="container">
	<div class="title bold">
		<?php printf("%s - %s", $company->EntityCd, $company->CompanyName); ?><br />
	</div>
	<div class="subTitle">
		<?php print($subTitle); ?><br />
		Periode: <?php printf("%s %s", $monthNames[$month], $year);
        if($selectedProject != null){
            print("<br/>");
            printf('Proyek : %s - %s', $selectedProject->ProjectCd, $selectedProject->ProjectName);
        }
        ?>
	</div><br /><br />

	<table cellpadding="0" cellspacing="0" class="tablePadding">
		<tr class="bold center">
			<td rowspan="2" class="bN bE bS bW">No. Akun</td>
			<td rowspan="2" class="bN bE bS">Nama Akun</td>
			<td colspan="2" class="bN bE bS">Mutasi <?php printf("%s %s", $monthNames[$month], $year); ?></td>
			<td colspan="2" class="bN bE bS">Jumlah s.d. <?php printf("%s %s", $monthNames[$month], $year); ?></td>
		</tr>
		<tr class="bold center">
			<td class="bE bS">Debet</td>
			<td class="bE bS">Kredit</td>
			<td class="bE bS">Debet</td>
			<td class="bE bS">Kredit</td>
		</tr>
		<?php
		$sumDebit = 0;
		$sumCredit = 0;
		$sumAllDebit = 0;
		$sumAllCredit = 0;
		while($row = $report->FetchAssoc()) {
			$sumDebit += $row["total_debit"];
			$sumCredit += $row["total_credit"];
			$sumAllDebit += $row["total_debit"] + $row["total_debit_prev"];
			$sumAllCredit += $row["total_credit"] + $row["total_credit_prev"];
			?>
			<tr>
				<td class="bE bW"><?php print($row["acc_no"]); ?></td>
				<td class="bE"><?php print($row["acc_name"]); ?></td>
				<td class="bE right"><?php print(number_format($row["total_debit"], 2)); ?></td>
				<td class="bE right"><?php print(number_format($row["total_credit"], 2)); ?></td>
				<td class="bE right"><?php print(number_format($row["total_debit"] + $row["total_debit_prev"], 2)); ?></td>
				<td class="bE right"><?php print(number_format($row["total_credit"] + $row["total_credit_prev"], 2)); ?></td>
			</tr>
		<?php } ?>
		<tr class="bold">
			<td colspan="2" class="bN bE bS bW right">TOTAL :</td>
			<td class="bN bE bS right"><?php print(number_format($sumDebit, 2)); ?></td>
			<td class="bN bE bS right"><?php print(number_format($sumCredit, 2)); ?></td>
			<td class="bN bE bS right"><?php print(number_format($sumAllDebit, 2)); ?></td>
			<td class="bN bE bS right"><?php print(number_format($sumAllCredit, 2)); ?></td>
		</tr>
	</table>
</div>
<?php } ?>
<!-- END REGION: LAPORAN-->


</body>
</html>
