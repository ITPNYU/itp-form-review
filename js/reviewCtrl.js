ifrApp.controller('ReviewCtrl', function ($scope, $http) {
  $http.get(ifr_api + 'review?blog=2') // FIXME
    .success(function(data) {
      $scope.reviews = data.objects;
    });

  $scope.getReviews = function(entry) {
    var entryReviews = [];
    for (var rIndex in $scope.reviews) {
      if ($scope.reviews[rIndex]['entry'] === entry) {
        entryReviews.push($scope.reviews[rIndex]);
      }
    }
    return entryReviews;
  };

  $scope.submitReview = function(form, entry, recommendation, comment) {
    var req = {
      "form": form,
      "entry": entry,
      "recommendation": recommendation,
      "comment": comment
    };
    $http.post(ifr_api + 'review?blog=2', req) // FIXME
      .success(function(data, status, headers, config) {
        console.log('success');
      })
      .error(function() {
        console.log('error');
      });
  };
});
