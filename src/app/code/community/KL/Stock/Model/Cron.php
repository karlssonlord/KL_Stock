<?php 

class KL_Stock_Model_Cron
{
    public function fire()
    {
        Mage::dispathEvent('time_to_fix_stock_status_was_reached');
    }
}