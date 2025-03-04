<label class="opalestate-label opalestate-label--price-input"><?php esc_html_e( 'Area', 'opalestate-pro' ); ?></label>
<div class="opalestate-price-input-wrap">
    <div class="opalestate-price-input opalestate-price-input--min">
        <input class="form-control" type="number" name="min_area" value="<?php echo esc_attr( $data['input_min'] ); ?>" placeholder="<?php esc_attr_e( 'Min Area', 'opalestate-pro' ); ?>">
        <span class="opalestate-price-currency"><?php echo esc_html( $data['unit'] ); ?></span>
    </div>

    <div class="opalestate-price-input-separator">
        <span><?php echo esc_html_x( '-', 'price input separator', 'opalestate-pro' ); ?></span>
    </div>

    <div class="opalestate-price-input opalestate-price-input--max">
        <input class="form-control" type="number" name="max_area" value="<?php echo esc_attr( $data['input_max'] ); ?>" placeholder="<?php esc_attr_e( 'Max Area', 'opalestate-pro' ); ?>">
        <span class="opalestate-price-currency"><?php echo esc_html( $data['unit'] ); ?></span>
    </div>
</div>
