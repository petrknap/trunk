var OrderAPI = OrderAPI || {};

if (!OrderAPI.url || !OrderAPI.url.get || !OrderAPI.url.add || !OrderAPI.url.remove) {
    alert('Error, missing URLs for OrderAPI object');
}

OrderAPI.modify_url = function (url) {
    return url + '?t=' + (new Date()).getTime();
};

OrderAPI.get = function () {
    return $.ajax({
        type: "GET",
        url: this.modify_url(this.url.get)
    });
};

OrderAPI.add = function (id, amount) {
    return $.ajax({
        type: "POST",
        url: this.modify_url(this.url.add),
        data: {
            id: id,
            amount: amount
        }
    });
};

OrderAPI.remove = function (id) {
    return $.ajax({
        type: "DELETE",
        url: this.modify_url(this.url.remove),
        data: {
            id: id
        }
    });
};
