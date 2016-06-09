<form id="arwp_form" action="" method="post">
    <?php wp_nonce_field('arwp_nonce'); ?>
    <p><strong>Тип сброса:</strong></p>
    <p>
        <label><input type="radio" name="arwp_type" class="arwp-type" value="re-install"> Re-install</label><br>
        <label><input type="radio" name="arwp_type" class="arwp-type" value="post-clear"> Очистка постов</label><br>
        <label><input type="radio" name="arwp_type" class="arwp-type" value="delete-theme"> Удаление тем</label><br>
        <label><input type="radio" name="arwp_type" class="arwp-type" value="delete-plugin"> Удаление плагинов</label><br>
        <label><input type="radio" name="arwp_type" class="arwp-type" value="deep-cleaning" required> Глубокая очистка</label>
    </p>
    <div class="re-install">
        <p><strong>После установки уктивировать текущие плагины или тему?</strong></p>
    </div>
    <div class="post-class">
        <p><strong>Какие типы постов вы хотите удалить?</strong></p>
        <p>
            <label><input type="checkbox" name="arwp_post_type[]" class="arwp-post-type" value="post"> Посты</label><br>
            <label><input type="checkbox" name="arwp_post_type[]" class="arwp-post-type" value="page"> Страницы</label><br>
            <label><input type="checkbox" name="arwp_post_type[]" class="arwp-post-type" value="menu"> Меню</label><br>
            <label><input type="checkbox" name="arwp_post_type[]" class="arwp-post-type" value="media"> Медиа</label><br>
            <label><input type="checkbox" name="arwp_post_type[]" class="arwp-post-type" value="all"> Все</label>
        </p>
    </div>
    <p>
        <label>Ключ безопастности:<br>
            <input id="arwp-input" type="text" name="arwp_input" autocomplete="off" autofocus required />
        </label>
    </p>
    <p><input id="arwp-button" name="arwp_button" type="submit" value="Запустить очистку" /></p>
</form>