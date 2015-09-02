<rn:meta controller_path="custom/instAgent/output/GenericFileAttachments"  
         compatibility_set="November '09+" />

            <br/><br/><ul>

            <? foreach($this->data['results'] as $fattach):
                $fileUrl = $fattach['url'];
                $fileName = $fattach['filename'];
                $fileSize = $fattach['size'];
                $fileIcon = $fattach['icon'];
                //$file_contents = file_get_contents($url);
            ?>
                <li>
                    <a href="<?=$fileUrl;?>" target="_blank"><?=$fileIcon;?><?=$fileName;?></a>
                    <span class="rn_FileSize">(<?=$fileSize;?>)</span>
                </li>
            <? endforeach;?>

            </ul>

