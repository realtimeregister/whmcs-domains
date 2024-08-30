<script type="text/javascript">
    const interval = setInterval(() => {
        if (window.jQuery) {
            $(function () {
                $('#contentarea').html({$contentJSON})
            });
            clearInterval(interval);
        }}, 10
    )
</script>