<?php

$this->data['header'] = 'Your password has expired';

$this->includeAtTemplateBase('includes/header.php');

?>
<p>
  The password for your <?= htmlentities($this->data['accountName']); ?>
  account expired on <?= htmlentities($this->data['expireOnDate']); ?>.
</p>
<p>
  You will need to update your password before you can continue to where you
  were going.
</p>
<p>
<form action="<?= htmlentities($this->data['formTarget']); ?>">
  
    <?php foreach ($this->data['formData'] as $name => $value): ?>
        <input type="hidden"
               name="<?= htmlentities($name); ?>"
               value="<?= htmlentities($value); ?>" />
    <?php endforeach; ?>
    
    <button type="submit" id="yesbutton" name="changepwd"
            style="padding: 4px 8px;">
        Update password
    </button>
</form>
<?php

$this->includeAtTemplateBase('includes/footer.php');
