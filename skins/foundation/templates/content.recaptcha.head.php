{if $RECAPTCHA}
<script src="https://www.google.com/recaptcha/api.js?onload=reCaptchaCallback&render=explicit" async defer></script>
{/if}
{if $RECAPTCHA=='3'}
<script>
var reCaptchaCallback = function() {
        $(".g-recaptcha" ).each(function() {
            var el = $(this);
            grecaptcha.render($(el).attr('id'), {
                'sitekey': '{$CONFIG.recaptcha_public_key}',
                'badge': '{$SKIN_CUSTOM.recaptcha_badge_position}',
                'callback': function(token) {
                    if($(el).attr("data-form-id")){
                        $('#'+$(el).attr("data-form-id")).submit();
                    } else {
                        $(el).parent().submit();
                    }
                }
            });
        });
    };
</script>
{/if}