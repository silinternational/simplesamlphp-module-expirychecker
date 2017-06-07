<?php
$this->data['header'] = $this->t('{expirychecker:warning:access_denied}');
$this->includeAtTemplateBase('includes/header.php');
?>

		<h2><?php echo $this->t('{expirychecker:warning:access_denied}');?></h2>
		<p><?php echo $this->t('{expirychecker:warning:no_access_to}', array('%ACCOUNTNAME%' => htmlspecialchars($this->data['accountName'])));?></p> 
		<p><?php echo $this->t('{expirychecker:warning:expiry_date_text}');?> <b><?php echo htmlspecialchars($this->data['expireOnDate']);?></b></p>
		<p><?php echo $this->t('{expirychecker:warning:contact_home}');?></p>
<?php
$this->includeAtTemplateBase('includes/footer.php');
?>
