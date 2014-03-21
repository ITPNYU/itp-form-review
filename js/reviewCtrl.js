ifrApp.controller("ReviewCtrl", function ($scope, $http) {
  $http.get(ifr_api + 'review?blog=2') // FIXME
    .success(function() {
      $scope.reviews = data.objects;
    });


  $scope.getReviews = function(id) {
    for (var rIndex in $scope.reviews) {
      if ($scope.reviews[rIndex]["id"] === id) {
        return $scope.reviews[rIndex]["reviews"];
      }
    }
  };

  $scope.submitReview = function(recommendation, comment) {
    $http.post(ifr_api + 'review?blog=2', req) // FIXME
      .success(function(data, status, headers, config) {
        console.log("success");
      })
      .error(function() {
        console.log("error");
      });
  };
});
