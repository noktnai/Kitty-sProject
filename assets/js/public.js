//index.php
let userAgent = window.navigator.userAgent.toLowerCase();
var defaultpicker = true;
if (userAgent.indexOf("windows") !== -1 || userAgent.indexOf("macintosh") !== -1) defaultpicker = false;
let url = "http://geoapi.heartrails.com/api/json?jsonp=?";

function changeCategory() {
    let category = $("select[name='categories'] option:selected").val();
    resetObjects();
    if (category !== 'カテゴリー') {
        $.ajax({
            type: "GET",
            url: "assets/ajax.php",
            data: { category: category }
        }).done(function (response) {
            setObjects(response);
        }).fail(function () {
            alert('更新に失敗しました。');
        });
    }
}
function setObjects(json) {
    let objects = JSON.parse(json);
    for (let index = 0; index < objects.length; index++) {
        let option = $(document.createElement('option'));
        option.text(objects[index].name);
        option.val(objects[index].name);
        $('select[name="objects"]').append(option);
        $('select[name="objects"]').prop("disabled", false);
    }
}

function changePrefecture() {
    let prefecture = $("select[name='prefectures'] option:selected").val();
    resetCities();
    resetTowns();
    if (prefecture !== '都道府県') {
        $.getJSON(url, { "method": "getCities", "prefecture": prefecture }, setCities);
    }
}

function setCities(json) {
    let cities = json.response['location'];
    for (let index = 0; index < cities.length; index++) {
        let option = $(document.createElement('option'));
        option.text(cities[index].city);
        option.val(cities[index].city);
        $('select[name="cities"]').append(option);
        $('select[name="cities"]').prop("disabled", false);
        $('select[name="cities"]').prop("required", true);
    }
}

function changeCity() {
    let city = $('select[name="cities"] option:selected').val();
    resetTowns();
    if (city !== '市区') {
        $.getJSON(url, { 'method': 'getTowns', 'city': city }, setTowns);
    }
}

function setTowns(json) {
    let towns = sortTowns(json.response['location']);
    for (let index = 0; index < towns.length; index++) {
        let option = $(document.createElement('option'));
        option.text(towns[index]);
        option.val(towns[index]);
        $('select[name="towns"]').append(option);
        $('select[name="towns"]').prop("disabled", false);
    }
}

function sortTowns(towns_default) {
    let towns = [];
    let tmp = null;
    for (let index = 0; index < towns_default.length; index++) {
        if (!towns_default[index].town.match(tmp) && towns_default[index].town !== '（その他）') {
            tmp = towns_default[index].town.replace('地階', '');
            if (tmp.match('丁目')) {
                tmp = towns_default[index].town.slice(0, -3)
            }
            tmp = tmp.replace('一丁', '');//大阪府堺市
            towns.push(tmp);
        }
    }
    let set = new Set(towns);
    towns = Array.from(set);
    return towns;
}

function resetObjects() {
    $('select[name="objects"]').prop("disabled", true);
    $('select[name="objects"]').html('<option value="">名称</option>');
}

function resetCities() {
    $('select[name="cities"]').prop("disabled", true);
    $('select[name="cities"]').html('<option value="">市区</option>');
}

function resetTowns() {
    $('select[name="towns"]').prop("disabled", true);
    $('select[name="towns"]').html('<option value="">町域</option>');
}
$(function () {
    if (defaultpicker) {
        $("#date > .form-row").empty();
        $("#date > .form-row").append('<div class="col-5"><input type="date" name="start" class="form-control"></input></div><div class="col-2 text-center"><label class="pt-2">&#65374</label></div><div class="col-5"><input type="date" name="finish" class="form-control"></input></div>');
        $("select").toggleClass('form-control d-block w-100');
    } else {
        if ($("#date .input-group").length) {
            $("#date .input-group").datetimepicker({
                dayViewHeaderFormat: 'YYYY年 MMMM',
                format: 'YYYY-MM-DD',
                locale: 'ja',
                showClose: true
            });
        }
    }

    $("#narrow").change(function (e) {
        let target = $(e.target)
        if (target.attr("name") === 'categories') {
            changeCategory();
        } else if (target.attr("name") === 'prefectures') {
            changePrefecture();
        } else if (target.attr("name") === 'cities') {
            changeCity();
        }
    });

    $("#narrow-button").click(function () {
        $("#narrow-button").toggleClass("btn-danger btn-success");
        if ($("#narrow-button").hasClass("btn-success")) {
            $("#narrow-button").text('絞り込み検索');
        } else {
            $("#narrow-button").html('<i class="fa fa-times"></i>&nbsp;閉じる&nbsp;');
        }
    });

    //show.php
    if ($("#objects_table").length) {
        $("#objects_table").DataTable({
            "paging": false,
            "info": false,
            columnDefs: [
                { targets: 0, width: "50%" }
            ],
            oLanguage: {
                /* 日本語化設定 */
                sLengthMenu: "表示　_MENU_　件", // 表示行数欄(例 = 表示 10 件)
                oPaginate: { // ページネーション欄
                    sNext: "次へ",
                    sPrevious: "前へ"
                },
                sInfo: "_TOTAL_ 件中 _START_件から_END_件 を表示しています", // 現在の表示欄(例 = 100 件中 20件から30件 を表示しています)
                sSearch: "検索 ", // 検索欄(例 = 検索 --)
                sZeroRecords: "表示するデータがありません", // 表示する行がない場合
                sInfoEmpty: "0 件中 0件 を表示しています", // 行が表示されていない場合
                sInfoFiltered: "全 _MAX_ 件から絞り込み" // 検索後に総件数を表示する場合
            }
        });
    }
});