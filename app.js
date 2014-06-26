var app = angular.module("wateringResources", []);

app.controller("cartListCtrl", function($scope, $http){
    this.carts;
    var curObj = this;
    $http({
        method: "POST",
        url: "WateringUtilPost.php",
        data: {"FUNC": "getAllCars"}
        }).success(function(data, status) {
            curObj.carts = data;
        }).error(function(data, status) {
            console.log("It broke, " + status);
            console.log(data);
            $(".error-tracker").html(data);
        });
    this.begin = function(actual){
        console.log("filtering?" + actual + expected);
        return actual.substring(0, expected.length) === expected;
    };
    $scope.beginsWith = function(query) {
        return function(cart) { 
            if (query == undefined){
                return true;
            }
            return cart.identcode.substring(0, query.length) === query;
        }
    };
});


