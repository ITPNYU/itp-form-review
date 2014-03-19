ifrApp.controller("ReviewCtrl", function ($scope) {
  $scope.reviews = [ {"id": "12", "reviews": [ { "reviewer": "abc123", "recommendation": "approve", "comment": "good", "date_created": "2014-03-19T19:17:32.353Z" } ] } ]; // FIXME: implement

  $scope.getReviews = function(id) {
    for (var rIndex in $scope.reviews) {
      if ($scope.reviews[rIndex]["id"] === id) {
        return $scope.reviews[rIndex]["reviews"];
      }
    }
  };
});
