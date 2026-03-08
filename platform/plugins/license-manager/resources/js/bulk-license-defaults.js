$(function () {
    var $productSelect = $('[name="product_reference_id"]')
    var $bulkToggle = $('[name="bulk_create"]')
    var isBulkPage = !$bulkToggle.length

    if (!$productSelect.length) {
        return
    }

    var defaultsUrl = $productSelect.data('defaults-url')

    if (!defaultsUrl) {
        return
    }

    var fieldMap = {
        default_license_type: 'license_type',
        default_uses: 'uses',
        default_parallel_uses: 'parallel_uses',
        default_expiry_days: 'expiry_days',
        default_comments: 'comments',
    }

    function fillDefaults(referenceId) {
        if (!referenceId) {
            return
        }

        var url = defaultsUrl.replace('__PRODUCT__', referenceId)

        $.getJSON(url)
            .done(function (data) {
                $.each(fieldMap, function (apiField, formField) {
                    var $field = $('[name="' + formField + '"]')
                    if ($field.length && !$field.val()) {
                        $field.val(data[apiField] ?? '')
                    }
                })
            })
            .fail(function () {
                // Silently ignore -- product may not have defaults
            })
    }

    $productSelect.on('change', function () {
        if (isBulkPage || ($bulkToggle.length && $bulkToggle.is(':checked'))) {
            fillDefaults($(this).val())
        }
    })

    $bulkToggle.on('change', function () {
        if ($(this).is(':checked') && $productSelect.val()) {
            fillDefaults($productSelect.val())
        }
    })

    // Auto-fill if pre-selected via query params
    if ($productSelect.val() && (isBulkPage || ($bulkToggle.length && $bulkToggle.is(':checked')))) {
        fillDefaults($productSelect.val())
    }
})
