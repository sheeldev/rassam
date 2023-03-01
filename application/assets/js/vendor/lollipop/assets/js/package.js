var app = angular.module("effectshop", ['ngAnimate']);
app.controller("PackageController", function($scope) {
    $scope.update = function() {
        $(".list-item").on("mouseenter", function() {
            $(this).addClass("active");
        });
        $(".list-item").on("mouseleave", function() {
            $(this).removeClass("active");
        });
    };
    $scope.applyFilter = function(filter) {
        applyFilter(filter);
    };
    $scope.filters = index;
    $scope.selection = selection;
});
