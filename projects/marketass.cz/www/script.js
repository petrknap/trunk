$(function () {
    $("#toggle-menu").click(function () {
        $("#menu, #content").parent().toggleClass("active");
    });

    function init() {
        if ($("#toggle-menu").is(":visible")) {
            $("#menu, #content").parent().addClass("off-canvas");
        } else {
            $("#menu, #content").parent().removeClass("off-canvas");
        }
    }

    $(window).resize(init);
    init();
});

var showModal = function (xhr, onSuccess, onFail) {
    var $modal = $(".js__modal"),
        $title = $modal.find(".js__modal_title"),
        $content = $modal.find(".js__modal_content"),
        $loading = $modal.find(".js__modal_loading");
    $content.hide();
    $loading.show();
    $title.hide();
    $modal.modal({keyboard: false});
    xhr.done(function (data) {
        onSuccess(data, $title, $content);
        $title.show();
        $loading.hide();
        $content.show();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        onFail();
        alert(errorThrown);
    });
};

OrderAPI.edit = function () {
    showModal(
        $.get(OrderAPI.modify_url(OrderAPI.url.edit)),
        function (data, $title, $content) {
            $title.html('Objednávka');
            $content.html(data);
            $content.find("a.btn-primary").on("click", OrderAPI.confirm);
        },
        null
    );
};

OrderAPI.confirm = function () {
    showModal(
        $.post(OrderAPI.modify_url(OrderAPI.url.confirm), $(this).closest("form").serialize()),
        function (data, $title, $content) {
            $title.html('Objednávka');
            $content.html(data);
            $content.find("a.btn-default").on("click", OrderAPI.edit);
            $content.find("a.btn-primary").on("click", OrderAPI.send);
            OrderAPI.get().done(OrderAPI.render_order);
        },
        OrderAPI.edit
    );
};

OrderAPI.send = function () {
    showModal(
        $.get(OrderAPI.modify_url(OrderAPI.url.send)),
        function (data, $title, $content) {
            $title.html('Objednávka');
            $content.html(data);
            OrderAPI.get().done(OrderAPI.render_order);
        },
        OrderAPI.edit
    );
};

OrderAPI.init = function () {
    OrderAPI.get().done(OrderAPI.render_order);

    $(".js__order").on("click", OrderAPI.edit);

    $(".js__add_to_order").on("click", function () {
        var $amount = $(this).parent().parent().find('input[type=number]');
        OrderAPI.add(
            $(this).data("url"),
            $amount.val()
        ).done(function (order) {
            $amount.val(1);
            OrderAPI.render_order(order);
        });
    });
};

OrderAPI.render_order = function (order) {
    var amount = 0, price = 0, formatter = function (number) {
        var formatted = number.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,').replace(/,/g, '&nbsp;').replace('.', ',');

        return formatted.substr(0, formatted.length - 3);
    };

    for (var id in order['items']) {
        if (order['items'].hasOwnProperty(id)) {
            amount += order['items'][id]['amount'];
            price += order['items'][id]['price'] * order['items'][id]['amount'];
        }
    }

    $(".js__order_amount").html(formatter(amount));
    $(".js__order_price").html(formatter(price));
    $(".js__order_customer").html(order['customer']['name'] ? order['customer']['name'] : '&nbsp;');
};
