<?php

$installer = $this;

$installer->startSetup();

// Some stores might already be set up to use the CreareSEO implementation of the GTM tag.
// In those cases, we want to take over that responsibility so we can do other things like check for consent for GDPR compliance.
Mage::helper('gtmdatalayer/setup_crearemigrate')->migrateFromCreareSeo();

$installer->endSetup();
