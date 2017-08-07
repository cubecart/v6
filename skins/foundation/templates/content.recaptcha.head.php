{if $RECAPTCHA=='2' || $RECAPTCHA=='3'}
<script src="https://www.google.com/recaptcha/api.js"></script>
{/if}
{if $RECAPTCHA=='3'}
<script>
function recaptchaSubmit(token) {
    $('.g-recaptcha').closest("form").submit();
}
</script>
{/if}