jQuery(document).ready(function () {
    ("use strict");

    // Tooltip
    jQuery(".tooltips").tooltip({ container: "body" });

    // Popover
    jQuery(".popovers").popover();

    // Show panel buttons when hovering panel heading
    jQuery(".panel-heading").hover(
        function () {
            jQuery(this).find(".panel-btns").fadeIn("fast");
        },
        function () {
            jQuery(this).find(".panel-btns").fadeOut("fast");
        }
    );

    // Close Panel
    jQuery(".panel .panel-close").click(function () {
        jQuery(this).closest(".panel").fadeOut(200);
        return false;
    });

    // Minimize Panel
    jQuery(".panel .panel-minimize").click(function () {
        var t = jQuery(this);
        var p = t.closest(".panel");
        if (!jQuery(this).hasClass("maximize")) {
            p.find(".panel-body, .panel-footer").slideUp(200);
            t.addClass("maximize");
            t.find("i").removeClass("fa-minus").addClass("fa-plus");
            jQuery(this)
                .attr("data-original-title", "Maximize Panel")
                .tooltip();
        } else {
            p.find(".panel-body, .panel-footer").slideDown(200);
            t.removeClass("maximize");
            t.find("i").removeClass("fa-plus").addClass("fa-minus");
            jQuery(this)
                .attr("data-original-title", "Minimize Panel")
                .tooltip();
        }
        return false;
    });

    jQuery(".leftpanel .nav .parent > a").click(function () {
        var coll = jQuery(this).parents(".collapsed").length;

        if (!coll) {
            jQuery(".leftpanel .nav .parent-focus").each(function () {
                jQuery(this).find(".children").slideUp("fast");
                jQuery(this).removeClass("parent-focus");
            });

            var child = jQuery(this).parent().find(".children");
            if (!child.is(":visible")) {
                child.slideDown("fast");
                if (!child.parent().hasClass("active"))
                    child.parent().addClass("parent-focus");
            } else {
                child.slideUp("fast");
                child.parent().removeClass("parent-focus");
            }
        }
        return false;
    });

    // Menu Toggle
    jQuery(".menu-collapse").click(function () {
        if (
            localStorage.getItem("menu_bloqueado") == "false" ||
            localStorage.getItem("menu_bloqueado") == null
        ) {
            if (!$("body").hasClass("hidden-left")) {
                if ($(".headerwrapper").hasClass("collapsed")) {
                    $(".headerwrapper, .mainwrapper").removeClass("collapsed");
                } else {
                    $(".headerwrapper, .mainwrapper").addClass("collapsed");
                    $(".children").hide(); // hide sub-menu if leave open
                }
            } else {
                if (!$("body").hasClass("show-left")) {
                    $("body").addClass("show-left");
                } else {
                    $("body").removeClass("show-left");
                }
            }
        }
        return false;
    });

    // Add class nav-hover to mene. Useful for viewing sub-menu
    jQuery(".leftpanel .nav li").hover(
        function () {
            $(this).addClass("nav-hover");
        },
        function () {
            $(this).removeClass("nav-hover");
        }
    );

    // For Media Queries
    jQuery(window).resize(function () {
        hideMenu();
    });

    hideMenu(); // for loading/refreshing the page
    function hideMenu() {
        if ($(".header-right").css("position") == "relative") {
            $("body").addClass("hidden-left");
            $(".headerwrapper, .mainwrapper").removeClass("collapsed");
        } else {
            $("body").removeClass("hidden-left");
        }

        // Seach form move to left
        if ($(window).width() <= 360) {
            if ($(".leftpanel .form-search").length == 0) {
                $(".form-search").insertAfter($(".profile-left"));
            }
        } else {
            if ($(".header-right .form-search").length == 0) {
                $(".form-search").insertBefore($(".btn-group-notification"));
            }
        }
    }

    // collapsedMenu(); // for loading/refreshing the page
    // function collapsedMenu() {
    //     if ($(".logo").css("position") == "relative") {
    //         $(".headerwrapper, .mainwrapper").addClass("collapsed");
    //     } else {
    //         $(".headerwrapper, .mainwrapper").removeClass("collapsed");
    //     }
    // }

    jQuery(".subCatalogoParent").on("click", function (e) {
        e.preventDefault();
        jQuery(this).next().toggle("fast", "linear");
    });

    //Modal de confirmacion de eliminacion
    jQuery(".delete").on("click", function (e) {
        e.preventDefault();
        swal({
            title: "¿Estás seguro de dar de baja este registro?",
            text: "Esta operación dará de baja este registro",
            icon: "warning",
            buttons: true,
            dangerMode: true,
            buttons: ["Cancelar", "Aceptar"],
        }).then((willDelete) => {
            if (willDelete) {
                jQuery(this).parent().submit();
            }
        });
    });

    //Tooltip
    // $('[data-toggle="tooltip"]').tooltip();

    //Datable Column hide/show
    const shTable = jQuery("#shTable").DataTable({
        responsive: true,
        paging: true,
        searching: false,
        scroll: true,
        language: language,
        autoWidth: true,
        deferRender: true,
        scrollCollapse: true,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    jQuery("#shTableDashBoard").DataTable({
        responsive: true,
        paging: true,
        searching: true,
        scroll: true,
        language: {
            ...language,
            search: "Buscador:",
        },
        autoWidth: true,
        deferRender: true,
        scrollCollapse: true,
        fnDrawCallback: function (oSettings) {
            jQuery("#shTable_paginate ul").addClass("pagination-active");
        },
    });

    // Show/Hide Columns Dropdown
    jQuery("#shCol").click(function (event) {
        event.stopPropagation();
    });

    const shCol_input = jQuery("#shCol input");

    shCol_input.on("click", function () {
        // Get the column API object
        const column = shTable.column($(this).val());
        // Toggle the visibility
        if ($(this).is(":checked")) column.visible(true);
        else column.visible(false);
    });

    const resetTableColumns = function () {
        // ocultamos las columnas que en su checkbos esten deshabilitados
        for (let index = 0; index < shCol_input.length; index++) {
            const column = shTable.column(shCol_input[index].value);
            if (!shCol_input[index].checked) {
                column.visible(false);
            }
        }
    };
    resetTableColumns();

    //funcionalidad clip menu
    let bodyMenu = $(".mainwrapper");
    let headMenu = $(".headerwrapper");
    //revisamos si el localStorage se encuentra el estado del menu
    let menuEstado = localStorage.getItem("menu_bloqueado");

    $("#clip-menu").click(() => {
        let menuEstado2 = localStorage.getItem("menu_bloqueado");
        if (headMenu.hasClass("collapsed")) {
            headMenu.removeClass("collapsed");
            bodyMenu.removeClass("collapsed");
            $("#clip-menu").css("background", "red");
            localStorage.setItem("menu_bloqueado", true);

            return false;
        }

        if (
            (headMenu.hasClass("collapsed") == false &&
                menuEstado2 == "false") ||
            menuEstado2 == null
        ) {
            $("#clip-menu").css("background", "red");
            localStorage.setItem("menu_bloqueado", true);
        } else {
            headMenu.addClass("collapsed");
            bodyMenu.addClass("collapsed");
            $("#clip-menu").css("background", "#E84E1B");
            localStorage.setItem("menu_bloqueado", false);
        }
    });

    if (menuEstado == "true") {
        headMenu.removeClass("collapsed");
        bodyMenu.removeClass("collapsed");
        $("#clip-menu").css("background", "red");
    }
});

// window.addEventListener("pageshow", function (event) {
//     if (event.persisted) {
//         window.location.reload();
//     }
// });
