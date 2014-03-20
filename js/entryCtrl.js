var ifrApp = angular.module("ifrApp", ["ngSanitize", "ui.bootstrap"]);
ifrApp.controller("EntryCtrl", function ($scope, $http) {
  $http.get(ifr_form_query).success(function(data) {
    // augment data with affiliations field
    var fields = ["6.1", "6.2", "6.3", "6.4", "6.5", "6.6"];
    for (var e in data.response.entries) {
      var affiliations = [];
      for (var f in fields) {
        if (data.response.entries[e][fields[f]] != "") {
          affiliations.push(data.response.entries[e][fields[f]]);
        }
      }
      data.response.entries[e]["affiliations"] = affiliations;
    }
    // now copy the data into scope
    $scope.entries = data.response.entries;
  });
});
