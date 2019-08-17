<!DOCTYPE HTML>
<html>
<?php
/** @var $start int */ /** @var $end int */ /** @var $docTypes DocType[] */ /** @var $showNo bool */ /** @var $showCol bool */ /** @var $docIds int[] */ /** @var $vocTypes VoucherType[] */
/** @var $report ReaderBase */ /** @var $output string */ /** @var $company Company */ /** @var $orientation string */ /** @var $status int */ /** @var $projectId int */ /** @var $projectList Project[] */
?>
<head>
	<title>Rekasys - Report Jurnal</title>
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
		$(document).ready(function(){
			$("#Start").customDatePicker({ phpDate: <?php print(is_int($start) ? $start : "null"); ?> });
			$("#End").customDatePicker({ phpDate: <?php print(is_int($end) ? $end : "NULL"); ?> });
		});

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
	<legend><span class="bold">Report Jurnal</span></legend>

	<form action="<?php print($helper->site_url("accounting.report/journal")); ?>" method="GET">
		<table cellpadding="0" cellspacing="0" class="tablePadding" style="margin: 0 auto; width: 80%;">
			<tr>
				<td class="nowrap right"><label for="Start">Periode : </label></td>
				<td>
					<input type="text" id="Start" name="start" />
					<label for="End"> s.d. </label>
					<input type="text" id="End" name="end" />
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
				<td class="nowrap right">Opsi :</td>
				<td>
					<input type="checkbox" id="ShowNo" name="showNo" value="1" <?php print($showNo ? 'checked="checked"' : '') ?> />
					<label for="ShowNo">Tambilkan No Akun bukan nama akun.</label><br />
					<input type="checkbox" id="ShowCol" name="showCol" value="1" <?php print($showCol ? 'checked="checked"' : '') ?> />
					<label for="ShowCol">Tampilkan kolom DEPT, DIV, PROJECT, DEBTOR, CREDITOR, dan KARYAWAN. (Kecuali format PDF)</label>
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
			$subTitle = "JURNAL: " . implode(", ", $buff) . " status: BELUM APPROVED";
			break;
		case 2:
			$subTitle = "JURNAL: " . implode(", ", $buff) . " status: SUDAH APPROVED";
			break;
		case 3:
			$subTitle = "JURNAL: " . implode(", ", $buff) . " status: VERIFIED";
			break;
		case 4:
			$subTitle = "JURNAL: " . implode(", ", $buff) . " status: POSTED";
			break;
		default:
			$subTitle = "JURNAL: " . implode(", ", $buff) . " status: SEMUA";
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
		Periode: <?php printf("%s s.d. %s", date(HUMAN_DATE, $start), date(HUMAN_DATE, $end)); ?>
        <?php
        if($selectedProject != null){
            print("<br/>");
            printf('Proyek : %s - %s', $selectedProject->ProjectCd, $selectedProject->ProjectName);
        }
        ?>
	</div><br /><br />

	<table cellpadding="0" cellspacing="0" class="tablePadding tableBorder">
		<tr class="bold center">
			<td rowspan="2">Tgl</td>
			<td rowspan="2">No. Voucher</td>
			<td rowspan="2">Company</td>
			<?php if ($showCol) { ?>
			<td rowspan="2">Dept</td>
			<td rowspan="2">Act</td>
			<td rowspan="2">Project</td>
			<td rowspan="2">Debtor</td>
			<td rowspan="2">Creditor</td>
			<td rowspan="2">Karyawan</td>
			<?php } ?>
			<td rowspan="2">Uraian</td>
			<td colspan="2">Debet</td>
			<td colspan="2">Kredit</td>
		</tr>
		<tr class="bold center">
			<td style="border-left: none;">Akun</td>
			<td>Jumlah</td>
			<td>Akun</td>
			<td>Jumlah</td>
		</tr>
		<?php
		$counter = 0;
		$prevDate = null;
		$prevVoucherNo = null;
		$prevSbu = null;

		$flagDate = true;
		$flagVoucherNo = true;
		$flagSbu = true;
		$sums = 0;
		while ($row = $report->FetchAssoc()) {
			// Convert datetime jadi native format
			$row["voucher_date"] = strtotime($row["voucher_date"]);
			$counter++;
			$className = $counter % 2 == 0 ? "itemRow evenRow" : "itemRow oddRow";
			if ($prevDate != $row["voucher_date"]) {
				$prevDate = $row["voucher_date"];
				$flagDate = true;
			} else {
				$flagDate = false;
			}

			if ($prevVoucherNo != $row["doc_no"]) {
				$prevVoucherNo = $row["doc_no"];
				$flagVoucherNo = true;
			} else {
				$flagVoucherNo = false;
			}

			if ($prevSbu != $row["entity_cd"]) {
				$prevSbu = $row["entity_cd"];
				$flagSbu = true;
			} else {
				$flagSbu = false;
			}

			if ($flagVoucherNo) {
				$link = sprintf('<a href="%s">%s</a>', $helper->site_url("accounting.voucher/view/" . $row["id"]), $prevVoucherNo);
			} else {
				$link = "&nbsp;";
			}

			$sums += $row["amount"];

			printf('<tr class="%s">', $className);
			printf('<td>%s</td>', $flagDate ? date("d", $prevDate) : "&nbsp;");
			printf('<td>%s</td>', $link);
			printf('<td>%s</td>', $flagSbu ? $prevSbu : "&nbsp;");
			if ($showCol) {
				printf('<td>%s</td>', $row["dept_code"]);
				printf('<td>%s</td>', $row["act_code"]);
				printf('<td>%s</td>', $row["project_name"]);
				printf('<td>%s</td>', $row["debtor_name"]);
				printf('<td>%s</td>', $row["creditor_name"]);
				printf('<td>%s</td>', $row["nama"]);
			}
			printf('<td>%s</td>', $row["note"]);
			printf('<td>%s</td>', $showNo ? $row["acc_no_debit"] : $row["acc_debit"]);
			printf('<td class="right">%s</td>', number_format($row["amount"], 2));
			printf('<td>%s</td>', $showNo ? $row["acc_no_credit"] : $row["acc_credit"]);
			printf('<td class="right">%s</td>', number_format($row["amount"], 2));
			print("</tr>");
		} ?>
		<tr class="bold right">
			<td colspan="<?php print($showCol ? 10 : 4); ?>">GRAND TOTAL :</td>
			<td>&nbsp;</td>
			<td><?php print(number_format($sums, 2)); ?></td>
			<td>&nbsp;</td>
			<td><?php print(number_format($sums, 2)); ?></td>
		</tr>
	</table>
</div>
<?php } ?>
<!-- END REGION: LAPORAN-->


</body>
</html>
