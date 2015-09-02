<rn:meta controller_path="custom/utils/PopupLink" 
    compatibility_set="November '09+"/>

    <script>
          $(document).ready(function() {
            $(".<?=$this->data['attrs']['class_name'];?> a").click(function(e) {
              e.preventDefault();
              $(".<?=$this->data['attrs']['class_name'];?>Text").fadeIn("slow");
            });
            $(".<?=$this->data['attrs']['class_name'];?>Text a").click(function(e) {
              e.preventDefault();
              $(".<?=$this->data['attrs']['class_name'];?>Text").fadeOut("slow");
            });
          });
    </script>

    <div class="<?=$this->data['attrs']['class_name'];?>Text popup">
      <h4><?=$this->data['attrs']['label_link'];?></h4>
      <?=$this->data['attrs']['popup_msg'];?>
      <p><a href="/app/instAgent/list#">[close]</a></p>
    </div>
    <p class="<?=$this->data['attrs']['class_name'];?>"><a href="/app/instAgent/list#"><?=$this->data['attrs']['label_link'];?></a></p>
