/* global $ */
/* this is an example for validation and change events */

const formatoMexico2 = (number) => {
    const exp = /(\d)(?=(\d{3})+(?!\d))/g;
    const rep = "$1,";
    let arr = number.toString().split(".");
    arr[0] = arr[0].replace(exp, rep);
    return arr[1] ? arr.join(".") : arr[0];
};

$.fn.numericInputExample = function () {
    "use strict";
    var element = $(this),
        footer = element.find("tfoot tr"),
        dataRows = element.find("tbody tr"),
        initialTotal = function () {
            var column, total;
            for (column = 3; column < footer.children().size(); column++) {
                total = 0;
                dataRows.each(function () {
                    var row = $(this);
                    if (column === 3) {
                        total +=
                            parseFloat(
                                row
                                    .children()
                                    .eq(column)
                                    .text()
                                    .replace(/[$,]/g, "")
                            ) *
                            parseFloat(
                                row
                                    .children()
                                    .eq(column + 1)
                                    .text()
                                    .replace(/[$,]/g, "")
                            );
                    } else {
                        total += parseFloat(
                            row
                                .children()
                                .eq(column)
                                .text()
                                .replace(/[$,]/g, "")
                        );
                    }
                });
                if (column === 3) {
                    let totalFormato = currency(total, {
                        separator: ",",
                        decimal: ".",
                        precision: 2,
                        formatWithSymbol: true,
                        symbol: "$",
                    });


                    footer
                        .children()
                        .eq(column)
                        .text('$'+totalFormato);
                } else {
                    let totalFormato = currency(total, {
                        separator: ",",
                        decimal: ".",
                        precision: 2,
                        formatWithSymbol: true,
                        symbol: "$",
                    });

                    footer.children().eq(column).text(totalFormato);
                }
            }
        };
    element
        .find("td")
        .on("change", function (evt) {
            var cell = $(this),
                column = cell.index(),
                totalCosto = 0;
            var total = 0;
            if (column === 0) {
                return;
            }
            
            element.find("tbody tr").each(function () {
                var row = $(this);
                if (column === 4) {
                    totalCosto +=
                        parseFloat(
                            row
                                .children()
                                .eq(column)
                                .text()
                                .replace(/[$,]/g, "")
                        ) *
                        parseFloat(
                            row
                                .children()
                                .eq(column - 1)
                                .text()
                                .replace(/[$,]/g, "")
                        );
                }
                total += parseFloat(
                    row.children().eq(column).text().replace(/[$,]/g, "")
                );
            });
            if (column === 1 && total > 5000) {
                $(".alert").show();
                return false; // changes can be rejected
            } else {
                $(".alert").hide();
                footer
                    .children()
                    .eq(column - 1)
                    .text(formatoMexico2(totalCosto));
                footer.children().eq(column).text(formatoMexico2(total));
            }
        })
        .on("validate", function (evt, value) {
            var cell = $(this),
                column = cell.index();
            if (column === 0) {
                return !!value && value.trim().length > 0;
            } else {
                return !isNaN(parseFloat(value)) && isFinite(value);
            }
        });
    initialTotal();
    return this;
};
