/* @projectDescription jQuery Password Strength Plugin - A jQuery plugin to provide accessibility functions
 * @author Tane Piper (digitalspaghetti@gmail.com)
 * @version 2.0
 * @website: http://digitalspaghetti.me.uk/digitalspaghetti
 * @license MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * === Changelog ===
 * Version 2.1 (18/05/2008)
 * Added a jQuery method to add a new rule: jQuery('input[@type=password]').pstrength.addRule(name, method, score, active)
 * Added a jQuery method to change a rule score: jQuery('input[@type=password]').pstrength.changeScore('one_number', 50);
 * Added a jQuery method to change a rules active state: jQuery('input[@type=password]').pstrength.ruleActive('one_number', false);
 * Hide the 'password to short' span if the password is more than the min chars
 *
 * Version 2.0 (17/05/2008)
 * Completly re-wrote the plugin from scratch.  Plugin now features lamda functions for validation and
 * custom validation rules
 * Plugin now exists in new digitalspaghetti. namespace to stop any conflits with other plugins.
 * Updated documentation
 *
 * Version 1.4 (12/02/2008)
 * Added some improvments to i18n stuff from Raffael Luthiger.
 * Version 1.3 (02/01/2008)
 * Changing coding style to more OO
 * Added default messages object for i18n
 * Changed password length score to Math.pow (thanks to Keith Mashinter for this suggestion)
 * Version 1.2 (03/09/2007)
 * Added more options for colors and common words
 * Added common words checked to see if words like 'password' or 'qwerty' are being entered
 * Added minimum characters required for password
 * Re-worked scoring system to give better results
 * Version 1.1 (20/08/2007)
 * Changed code to be more jQuery-like
 * Version 1.0 (20/07/2007)
 * Initial version.
 */

// Create our namespaced object
/*global window */
/*global jQuery */
/*global digitalspaghetti*/
window.digitalspaghetti = window.digitalspaghetti || {};
digitalspaghetti.password = {
	'defaults' : {
		'displayMinChar': true,
		'minChar': 8,
		'minCharText': 'You must enter a minimum of %d characters',
		'colors': ["#f00", "#c06", "#f60", "#3c0", "#3f0"],
		'scores': [20, 30, 43, 50],
		'verdicts':	['Weak', 'Normal', 'Medium', 'Strong', 'Very Strong'],
		'raisePower': 1.4,
		'debug': false
	},
	'ruleScores' : {
		'length': 0,
		'lowercase': 1,
		'uppercase': 3,
		'one_number': 3,
		'three_numbers': 5,
		'one_special_char': 3,
		'two_special_char': 5,
		'upper_lower_combo': 2,
		'letter_number_combo': 2,
		'letter_number_char_combo': 2
	},
	'rules' : {
		'length': true,
		'lowercase': true,
		'uppercase': true,
		'one_number': true,
		'three_numbers': true,
		'one_special_char': true,
		'two_special_char': true,
		'upper_lower_combo': true,
		'letter_number_combo': true,
		'letter_number_char_combo': true
	},
	'validationRules': {
		'length': function (word, score) {
			digitalspaghetti.password.tooShort = false;
			var wordlen = word.length;
			var lenScore = Math.pow(wordlen, digitalspaghetti.password.options.raisePower);
			if (wordlen < digitalspaghetti.password.options.minChar) {
				lenScore = (lenScore - 100);
				digitalspaghetti.password.tooShort = true;
			}
			return lenScore;
		},
		'lowercase': function (word, score) {
			return word.match(/[a-z]/) && score;
		},
		'uppercase': function (word, score) {
			return word.match(/[A-Z]/) && score;
		},
		'one_number': function (word, score) {
			return word.match(/\d+/) && score;
		},
		'three_numbers': function (word, score) {
			return word.match(/(.*[0-9].*[0-9].*[0-9])/) && score;
		},
		'one_special_char': function (word, score) {
			return word.match(/.[!,@,#,$,%,\^,&,*,?,_,~]/) && score;
		},
		'two_special_char': function (word, score) {
			return word.match(/(.*[!,@,#,$,%,\^,&,*,?,_,~].*[!,@,#,$,%,\^,&,*,?,_,~])/) && score;
		},
		'upper_lower_combo': function (word, score) {
			return word.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/) && score;
		},
		'letter_number_combo': function (word, score) {
			return word.match(/([a-zA-Z])/) && word.match(/([0-9])/) && score;
		},
		'letter_number_char_combo' : function (word, score) {
			return word.match(/([a-zA-Z0-9].*[!,@,#,$,%,\^,&,*,?,_,~])|([!,@,#,$,%,\^,&,*,?,_,~].*[a-zA-Z0-9])/) && score;
		}
	},
	'attachWidget': function (element) {
		var output = ['<div id="password-strength">'];
		if (digitalspaghetti.password.options.displayMinChar && !digitalspaghetti.password.tooShort) {
			output.push('<span class="password-min-char">' + digitalspaghetti.password.options.minCharText.replace('%d', digitalspaghetti.password.options.minChar) + '</span>');
		}
		output.push('<span class="password-strength-bar"></span>');
		output.push('</div>');
		output = output.join('');
		jQuery(element).after(output);
	},
	'debugOutput': function (element) {
		if (typeof console.log === 'function') {
			console.log(digitalspaghetti.password);
		} else {
			alert(digitalspaghetti.password);
		}
	},
	'addRule': function (name, method, score, active) {
		digitalspaghetti.password.rules[name] = active;
		digitalspaghetti.password.ruleScores[name] = score;
		digitalspaghetti.password.validationRules[name] = method;
		return true;
	},
	'init': function (element, options) {
		digitalspaghetti.password.options = jQuery.extend({}, digitalspaghetti.password.defaults, options);
		digitalspaghetti.password.attachWidget(element);
		jQuery(element).keyup(function () {
			digitalspaghetti.password.calculateScore(this, jQuery(this).val());
		});
		if (digitalspaghetti.password.options.debug) {
			digitalspaghetti.password.debugOutput();
		}
	},
	'calculateScore': function (element, word) {
		digitalspaghetti.password.totalscore = 0;
		digitalspaghetti.password.width = 0;
		for (var key in digitalspaghetti.password.rules) if (digitalspaghetti.password.rules.hasOwnProperty(key)) {
			if (digitalspaghetti.password.rules[key] === true) {
				var score = digitalspaghetti.password.ruleScores[key];
				var result = digitalspaghetti.password.validationRules[key](word, score);
				if (result) {
					digitalspaghetti.password.totalscore += result;
				}
			}
			if (digitalspaghetti.password.totalscore <= digitalspaghetti.password.options.scores[0]) {
				digitalspaghetti.password.strColor = digitalspaghetti.password.options.colors[0];
				digitalspaghetti.password.strText = digitalspaghetti.password.options.verdicts[0];
				digitalspaghetti.password.width =  "1";
			} else if (digitalspaghetti.password.totalscore > digitalspaghetti.password.options.scores[0] && digitalspaghetti.password.totalscore <= digitalspaghetti.password.options.scores[1]) {
				digitalspaghetti.password.strColor = digitalspaghetti.password.options.colors[1];
				digitalspaghetti.password.strText = digitalspaghetti.password.options.verdicts[1];
				digitalspaghetti.password.width =  "25";
			} else if (digitalspaghetti.password.totalscore > digitalspaghetti.password.options.scores[1] && digitalspaghetti.password.totalscore <= digitalspaghetti.password.options.scores[2]) {
				digitalspaghetti.password.strColor = digitalspaghetti.password.options.colors[2];
				digitalspaghetti.password.strText = digitalspaghetti.password.options.verdicts[2];
				digitalspaghetti.password.width =  "50";
			} else if (digitalspaghetti.password.totalscore > digitalspaghetti.password.options.scores[2] && digitalspaghetti.password.totalscore <= digitalspaghetti.password.options.scores[3]) {
				digitalspaghetti.password.strColor = digitalspaghetti.password.options.colors[3];
				digitalspaghetti.password.strText = digitalspaghetti.password.options.verdicts[3];
				digitalspaghetti.password.width =  "75";
			} else {
				digitalspaghetti.password.strColor = digitalspaghetti.password.options.colors[4];
				digitalspaghetti.password.strText = digitalspaghetti.password.options.verdicts[4];
				digitalspaghetti.password.width =  "99";
			}
			jQuery('.password-strength-bar').stop();
			if (digitalspaghetti.password.options.displayMinChar && !digitalspaghetti.password.tooShort) {
				jQuery('.password-min-char').hide();
			} else {
				jQuery('.password-min-char').show();
			}

			jQuery('.password-strength-bar').animate({opacity: 0.5}, 'fast', 'linear', function () {
				jQuery(this).css({'display': 'block', 'background-color': digitalspaghetti.password.strColor, 'width': digitalspaghetti.password.width + "%"}).text(digitalspaghetti.password.strText);
				jQuery(this).animate({opacity: 1}, 'fast', 'linear');
			});
		}
		/* CubeCart Patch - 2010/01/27 */
		jQuery(element).removeClass('required-error');
		if (digitalspaghetti.password.width < digitalspaghetti.password.options.scores[3]) {
			jQuery(element).addClass('required-error');

		}
		/* End Patch */
	}
};

jQuery.extend(jQuery.fn, {
	'pstrength': function (options) {
		return this.each(function () {
			digitalspaghetti.password.init(this, options);
		});
	}
});
jQuery.extend(jQuery.fn.pstrength, {
	'addRule': function (name, method, score, active) {
		digitalspaghetti.password.addRule(name, method, score, active);
		return true;
	},
	'changeScore': function (rule, score) {
		digitalspaghetti.password.ruleScores[rule] = score;
		return true;
	},
	'ruleActive': function (rule, active) {
		digitalspaghetti.password.rules[rule] = active;
		return true;
	}
});