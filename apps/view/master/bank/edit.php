<!DOCTYPE HTML>
<html>
<head>
    <title>Rekasys - Edit Bank</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/jquery-ui.css")); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php print($helper->path("public/css/common.css")); ?>"/>

    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/jquery-ui.custom.min.js")); ?>"></script>
    <script type="text/javascript" src="<?php print($helper->path("public/js/common.js")); ?>"></script>
</head>

<body>
<?php /** @var $company Company */ /** @var $bank Bank */ /** @var $cashAccounts Coa[] */ /** @var $costAccounts Coa[] */ /** @var $revenueAccounts Coa[] */ ?>
<?php include(VIEW . "main/menu.php"); ?>
<?php if (isset($error)) { ?>
    <div class="ui-state-error subTitle center"><?php print($error); ?></div><?php } ?>
<?php if (isset($info)) { ?>
    <div class="ui-state-highlight subTitle center"><?php print($info); ?></div><?php } ?>
<br />
<fieldset>
    <legend><span class="bold">Edit Bank</span></legend>
    <form action="<?php print($helper->site_url("master.bank/edit/".$bank->Id)); ?>" method="post">
        <table cellspacing="0" cellpadding="0" class="tablePadding">
            <tr>
                <td class="right bold">Company :</td>
                <td><?php printf('%s - %s', $company->EntityCd, $company->CompanyName) ?></td>
            </tr>
            <tr>
                <td class="bold right"><label for="Name">Bank Name :</label></td>
                <td><input type="text" id="Name" name="Name" value="<?php print($bank->Name); ?>" size="30" onkeyup="this.value = this.value.toUpperCase();" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="Branch">Branch :</label></td>
                <td><input type="text" id="Branch" name="Branch" value="<?php print($bank->Branch); ?>" size="15" onkeyup="this.value = this.value.toUpperCase();" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="Address">Address :</label></td>
                <td><input type="text" id="Address" name="Address" value="<?php print($bank->Address); ?>" size="50" /></td>
            </tr>
            <tr>
                <td class="bold right"><label for="NoRek">Account No. :</label></td>
                <td><input type="text" id="NoRek" name="NoRek" value="<?php print($bank->NoRekening); ?>" size="30" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="CurrencyCode">Currency :</label></td>
                <td><input type="text" id="CurrencyCode" name="CurrencyCode" value="<?php print($bank->CurrencyCode); ?>" size="5" onkeyup="this.value = this.value.toUpperCase();" required/></td>
            </tr>
            <tr>
                <td class="bold right"><label for="AccId">Control Account :</label></td>
                <td><select id="AccId" name="AccId">
                        <option value="">-- PILIH AKUN --</option>
                        <?php
                        foreach ($cashAccounts as $account) {
                            if ($account->Id == $bank->AccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td class="bold right"><label for="CostAccId">Cost Account :</label></td>
                <td><select id="CostAccId" name="CostAccId">
                        <option value="">-- PILIH AKUN --</option>
                        <?php
                        foreach ($costAccounts as $account) {
                            if ($account->Id == $bank->CostAccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td class="bold right"><label for="RevAccId">Revenue Account :</label></td>
                <td><select id="RevAccId" name="RevAccId">
                        <option value="">-- PILIH AKUN --</option>
                        <?php
                        foreach ($revenueAccounts as $account) {
                            if ($account->Id == $bank->RevAccId) {
                                printf('<option value="%d" selected="selected">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
                            } else {
                                printf('<option value="%d">%s - %s</option>', $account->Id, $account->AccNo, $account->AccName);
                            }
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><button type="submit">Submit</button>
                    &nbsp;
                    <a href="<?php print($helper->site_url("master.bank")); ?>">Master Bank List</a>
                </td>
            </tr>
        </table>
    </form>
</fieldset>

</body>
</html>
