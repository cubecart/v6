<div class="wrap">
    <h2>
        <a href="http://www.shareyourcart.com" target="_blank" title="Shareyourcart" class="shareyourcart-logo">
            <img src="<?php echo $this->createUrl(dirname(__FILE__).'/../img/shareyourcart-logo.png'); ?>"/>
        </a>
        <div class="syc-slogan">Increase your social media exposure by 10%!</div>
        <br clear="all" /> 
    </h2>

    
	<div class="updated settings-error"><p><strong>
    <?php echo $status_message; ?>
	</strong></p></div>
	  
    <div id="visual-options">
        <form method="POST">
            <table class="form-table" name="shareyourcart_settings">
                <tr>
                    <th scope="row">Button skin</th>
                    <td>
                        <select name="button_skin" id="button_skin">
                            <option name="orange" <?php echo $current_skin == 'orange' ? 'selected="selected"' : ''; ?> value="orange">Orange</option>
                            <option name="blue" <?php echo $current_skin == 'blue' ? 'selected="selected"' : ''; ?> value="blue">Blue</option>
                        </select>                        
                    </td>
                </tr>
                <tr>
                    <th scope="row">Button position</th>
                    <td>
                        <select name="button_position" id="button_position">
                            <option name="normal" <?php echo $current_position == 'normal' ? 'selected="selected"' : ''; ?> value="normal">Normal</option>
                            <option name="floating" <?php echo $current_position == 'floating' ? 'selected="selected"' : ''; ?> value="floating">Floating</option>
                        </select>                        
                    </td>
                </tr>
				<tr>
                    <th scope="row">Show by default on</th>
                    <td>
                            <input name="show_on_product" <?php echo $show_on_product ? 'checked="checked"' : ''; ?>  type='checkbox'>Product page</input>
                            <input name="show_on_checkout" <?php echo $show_on_checkout ? 'checked="checked"' : ''; ?> type='checkbox'>Checkout page</input>                        
                    </td>
                </tr>
            </table>
            <div class="submit"><input type="submit" value="Save"></div> 
            <input type="hidden" name="visual-form" value="visual-form"/>
        </form>
    </div>
	
	<h2>Contact</h2>
	<p>You can find help in our <a href="http://shareyourcart.uservoice.com" target="_blank" title="forum">forum</a>, or if you have a private question, you can <a href="http://www.shareyourcart.com/contact" target="_blank">contact us directly</a></p>
	<br />
</div>