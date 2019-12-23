<h1>Network Posts Thumbnails Resizer</h1>
<form method="post">
    <fieldset>
        <input type="hidden" name="_wpnonce" value="<?= $data['nonce']; ?>"/>
        <table class="form-table">
            <thead>
            <tr>
                <th>Blog name</th>
                <th>Resizing allowed</th>
                <th>Global</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($data['rows'] as $row):?>
            <tr>
                <td><?= $row['blogname']; ?></td>
                <td class="check-column">
                    <input type="checkbox" name="allowed[]" value="<?= $row['id']; ?>" <?= $row['allowed']; ?> />
                </td>
                <td class="check-column">
                    <input type="checkbox" name="global[]" value="<?= $row['id']; ?>" <?= $row['global']; ?> />
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save"/>
    </fieldset>
</form>
