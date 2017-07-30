#!/usr/bin/php -q
# // Script to auto reboot rigs with stuck_miners condition

<?php
        $ethosurl = "http://hashr9.ethosdistro.com/?json=yes";
        $json6 = file_get_contents($ethosurl);
        $ethosjson = json_decode($json6);

        foreach($ethosjson->rigs as $rig_id => $k) {
         if(is_array($ethosjson->rigs) || is_object($ethosjson->rigs)) {

            // If rig has stuck miners, reboot it
            if($k->condition == "stuck_miners") {
                if($k->miner == "sgminer-gm") {
                        $deadgpu = substr_count($k->mem,'300');
                        if($deadgpu >= 3) {
                                echo 'Miner restarted on ', $rig_id, ' because it has ', $deadgpu, ' stuck gpus', PHP_EOL;
                                exec("timeout 30 sshpass -f /root/gpurigs/gpupass.txt ssh -oStrictHostKeyChecking=no -oConnectTimeout=3 -l root {$k->ip} 'minestop'", $output);
                        } else {
                                echo '[WARN] Reboot ', $rig_id, ' because it has stuck gpus', PHP_EOL;
                                exec("timeout 30 sshpass -f /root/gpurigs/gpupass.txt ssh -oStrictHostKeyChecking=no -oConnectTimeout=3 -l root {$k->ip} 'shutdown -r now'", $output);
                        }
                        echo '';
                } else {
                        echo '[WARN] Reboot ', $rig_id, ' because it has stuck gpus', PHP_EOL;
                        exec("timeout 30 sshpass -f /root/gpurigs/gpupass.txt ssh -oStrictHostKeyChecking=no -oConnectTimeout=3 -l root {$k->ip} 'shutdown -r now'", $output);
                }
            } elseif($k->condition == "throttle") {
                echo 'Clearing thermals and updating ', $rig_id, ' because it is throttled', PHP_EOL;
                exec("timeout 30 sshpass -f /root/gpurigs/gpupass.txt ssh -oStrictHostKeyChecking=no -oConnectTimeout=3 -l root {$k->ip} 'clear-thermals && update'", $output);
                echo '';
            } elseif($k->condition == "autorebooted") {
                echo 'Clearing thermals and updating ', $rig_id, ' because it has been autorebooted', PHP_EOL;
                exec("timeout 30 sshpass -f /root/gpurigs/gpupass.txt ssh -oStrictHostKeyChecking=no -oConnectTimeout=3 -l root {$k->ip} 'clear-thermals && update'", $output);
                echo '';
            } elseif($k->condition == "overheat") {
                echo 'Clearing thermals and Rebooting ', $rig_id, ' because it has overheated', PHP_EOL;
                exec("timeout 30 sshpass -f /root/gpurigs/gpupass.txt ssh -oStrictHostKeyChecking=no -oConnectTimeout=3 -l root {$k->ip} 'clear-thermals && shutdown -r now'", $output);
                echo '';
            } elseif($k->condition == "no_hash") {
                echo 'Attempting to re-parse hashrate speeds from ', $rig_id, ' because they didnt show up last time', PHP_EOL;
                exec("timeout 30 sshpass -f /root/gpurigs/gpupass.txt ssh -oStrictHostKeyChecking=no -oConnectTimeout=3 -l root {$k->ip} 'update'", $output);
                echo '';
            }

         }
        }
?>

