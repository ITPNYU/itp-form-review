ifrApp.controller("PaymentCtrl", function ($scope, $http) {
  $http.get(ifr_api + 'payment?blog=2') // FIXME
    .success(function(data) {
      $scope.payments = data.objects;
    });

  $scope.getPayment = function(entry) {
    for (var dIndex in $scope.payments) {
      if ($scope.payments[dIndex]['entry'] == entry) {
        return $scope.payments[dIndex]['payment'];
      }
    }
    return null;
  };
});