var Lock = function () {

    return {
        //main function to initiate the module
        init: function () {

             $.backstretch([
		        "./Public/Assets/img/bg/1.jpg",
		        "./Public/Assets/img/bg/2.jpg",
		        "./Public/Assets/img/bg/3.jpg",
		        "./Public/Assets/img/bg/4.jpg"
		        ], {
		          fade: 1000,
		          duration: 8000
		      });
        }

    };

}();