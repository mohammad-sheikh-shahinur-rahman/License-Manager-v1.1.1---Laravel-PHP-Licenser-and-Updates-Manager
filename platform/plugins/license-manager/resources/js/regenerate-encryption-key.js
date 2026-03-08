$(function () {
    var $modal = $('#modal-regenerate-encryption-key')

    if (!$modal.length) {
        return
    }

    var $input = $('#regenerate-key-confirm-input')
    var $btn = $('#regenerate-key-confirm-btn')
    var confirmPhrase = $modal.data('confirm-phrase')
    var route = $modal.data('route')
    var buttonLabel = $btn.data('button-label')

    $input.on('input', function () {
        $btn.prop('disabled', $input.val().trim() !== confirmPhrase)
    })

    $modal.on('hidden.bs.modal', function () {
        $input.val('')
        $btn.prop('disabled', true)
    })

    $btn.on('click', function () {
        $btn.prop('disabled', true).text('Processing...')

        $.ajax({
            url: route,
            type: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                $modal.modal('hide')
                Botble.showSuccess(res.message)
                setTimeout(function () {
                    location.reload()
                }, 1500)
            },
            error: function (xhr) {
                $modal.modal('hide')
                Botble.showError(xhr.responseJSON?.message || 'An error occurred.')
                $btn.prop('disabled', false).text(buttonLabel)
            },
        })
    })
})
