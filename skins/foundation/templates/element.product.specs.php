<div class="content{if empty($PRODUCT.description)} active{/if}" id="product_spec">
    <table>
        <tbody>
            <tr>
                <td>{$LANG.catalogue.product_code}</td>
                <td>{$PRODUCT.product_code}</td>
            </tr>
            {if $PRODUCT.manufacturer}
            <tr>
                <td>{$LANG.catalogue.manufacturer}</td>
                <td>{$MANUFACTURER}</td>
            </tr>
            {/if}
            {if $PRODUCT.stock_level}
            <tr>
                <td>{$LANG.catalogue.stock_level}</td>
                <td>{$PRODUCT.stock_level}</td>
            </tr>
            {/if}
            <tr>
                <td>{$LANG.common.condition}</td>
                <td>{$PRODUCT.condition}</td>
            </tr>
            {if $PRODUCT.product_weight > 0}
            <tr>
                <td>{$LANG.common.weight}</td>
                <td>{$PRODUCT.product_weight}{$CONFIG.product_weight_unit|lower}</td>
            </tr>
            {/if}
            {if $PRODUCT.product_width > 0}
            <tr>
                <td>{$LANG.common.width}</td>
                <td>{floatval($PRODUCT.product_width)}{if $PRODUCT.dimension_unit=='in'}&#8243;{else}{$PRODUCT.dimension_unit}{/if}</td>
            </tr>
            {/if}
            {if $PRODUCT.product_height > 0}
            <tr>
                <td>{$LANG.common.height}</td>
                <td>{floatval($PRODUCT.product_height)}{if $PRODUCT.dimension_unit=='in'}&#8243;{else}{$PRODUCT.dimension_unit}{/if}</td>
            </tr>
            {/if}
            {if $PRODUCT.product_depth > 0}
            <tr>
                <td>{$LANG.common.depth}</td>
                <td>{floatval($PRODUCT.product_depth)}{if $PRODUCT.dimension_unit=='in'}&#8243;{else}{$PRODUCT.dimension_unit}{/if}</td>
            </tr>
            {/if}
            {if $PRODUCT.digital > 0}
            <tr>
                <td>{$LANG.catalogue.product_type_digital}</td>
                <td>{$LANG.common.download}</td>
            </tr>
            {/if}
        </tbody>
    </table>
</div>