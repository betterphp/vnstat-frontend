var http = {

	responseHandler: function(request, details){
		return function(){
			if (this.readyState == 4){
				if (this.status == 200){
					if (typeof details.onSuccess == 'function'){
						details.onSuccess(this.responseText);
					}
				}else{
					if (typeof details.onFailure == 'function'){
						details.onFailure();
					}
				}
			}
		};
	},

	get: function(details){
		var request = new XMLHttpRequest();
		var handler = this.responseHandler(request, details);

		request.addEventListener('readystatechange', handler, false);

		request.timeout = (details.timeout) ? details.timeout : 5000;

		request.open('GET', details.url, true);
		request.setRequestHeader('Cookie', document.cookie);
		request.send(null);
	},

	post: function(details){
		var request = new XMLHttpRequest();
		var handler = this.responseHandler(request, details);

		request.addEventListener('readystatechange', handler, false);

		request.timeout = (details.timeout) ? details.timeout : 5000;

		parameters = '';

		for (var key in details.data){
			parameters += key + '=' + details.data[key] + '&';
		}

		parameters = parameters.substr(0, parameters.length - 1);

		request.open('POST', details.url, true);
		request.setRequestHeader('Cookie', document.cookie);
		request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		request.setRequestHeader('Content-Length', parameters.length);
		request.send(parameters);
	}

};
