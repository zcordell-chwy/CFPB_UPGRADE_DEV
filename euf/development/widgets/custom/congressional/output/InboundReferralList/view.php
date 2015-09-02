<rn:meta controller_path="custom/congressional/output/InboundReferralList"/>

<? if( count( $this->data['activeReferrals'] ) > 0 ): ?>
    <div id="rn_<?= $this->instanceID; ?>">
        <h3 class="rn_HeadingBar">Referring Office(s)</h3>

        <? foreach( $this->data['activeReferrals'] as $counter => $referral ): ?>

            <h5><?= $referral->Organization->Name; ?></h5>

            <ul class="rn_FileAttachmentUpload2">

                <? foreach( $this->data['attachments'][$counter] as $fattach ):
                    $fileUrl = $fattach['url'];
                    $fileName = $fattach['filename'];
                    $fileSize = $fattach['size'];
                    $fileIcon = $fattach['icon'];
                ?>

                <li>
                    <a href="<?= $fileUrl; ?>" target="_blank"><?= $fileIcon; ?><?= $fileName ;?></a>
                    <span class="rn_FileSize">(<?= $fileSize; ?>)</span>
                </li>

                <? endforeach; ?>

            </ul>

        <? endforeach; ?>

    </div>
<? endif; ?>