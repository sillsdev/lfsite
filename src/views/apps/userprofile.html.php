<div class="container" ng-app="userProfile" ng-cloak>
	<div ng-controller="userProfileCtrl">
				<form ng-submit="updateUser()">
					<fieldset>
						<legend>{{user.name}}'s User Profile</legend>
						<div ng-show="notify.error" class="notification errorMessage">Error: {{notify.error}}</div>
						<div ng-show="notify.message" class="notification informationMessage">{{notify.message}}</div>
						<tabset>
							<tab heading="My Account">
								<label>Username</label>
								<input type="text" placeholder="(username)" ng-model="user.username"/>
								<label>Picture</label>
								<img class="img-polaroid" src="/images/avatar/anonymous02.png" />
								<label>Full Name</label>
								<input type="text" placeholder="(full name)" ng-model="user.name"/>
								<label>Email Address</label>
								<input type="text" placeholder="(email)" ng-model="user.email"/>
								<label>Jamaica Mobile Phone Number</label>
								<input type="text" placeholder="###-####" ng-model="user.mobile_phone"/>
								<label>I preferred to be contacted via</label>
								<!--  CH - TODO make this work right (model is not updating, and it submits on every button press -->
								<div class="btn-group">
									<a class="btn" ng-model="contact_method" btn-radio=" 'email' ">Email</a>
									<a class="btn" ng-model="contact_method" btn-radio=" 'sms' ">SMS</a>
									<a class="btn" ng-model="contact_method" btn-radio=" 'both' ">Email and SMS</a>
								</div>
								
								<label style="margin-top:20px"><a href="/app/changepassword">Change Password</a></label>
							</tab>
							<tab heading="About Me">
								<h5>Tell us about yourself...</h5>
								<label>Age</label>
								<input type="text" placeholder="(age)" ng-model="user.age"/>
								<label>Gender</label>
								<select ng-model="user.gender">
									<option>Male</option>
									<option>Female</option>
								</select>
								<label>Location</label>
								<select ng-model="user.city" ng-include src=" '/angular-app/userprofile/jamaica_towns.html '"/></select>
							</tab>
							<tab heading="Participation Details">
								<label>Preferred Bible Version</label>
								<select ng-model="user.preferred_bible_version" ng-include src=" '/angular-app/userprofile/jamaica_bible_versions.html' "/></select>
								<label>Religious Affiliation</label>
								<select ng-model="user.religious_affiliation" ng-include src=" '/angular-app/userprofile/jamaica_religious_affiliations.html' "/></select>
								<label>Study Group</label>
								<select ng-model="user.study_group" ng-include src=" '/angular-app/userprofile/jamaica_study_groups.html' "/></select>
								<label>Feedback Group</label>
								<select ng-model="user.feedback_group" ng-include src=" '/angular-app/userprofile/jamaica_feedback_groups.html' "/></select>
							</tab>
						</tabset>
						<button style="margin-top:20px" type="submit" class="btn">Save Profile</button>
						<div style="margin-top:20px" class="well">
							<label>Last Login:  {{user.last_login * 1000 | date:shortDate}}</label>
						</div>
					</fieldset>
				</form>
	</div>
</div>
	
<script type="text/javascript">
window.session = <?php echo $jsSessionVars; ?>
</script>
	
	
	
	
	
<script	src="/js/lib/angular_stable_1.0.7/angular.js"></script>
<script	src="/js/lib/ng-ui-bootstrap-tpls-0.4.0.js"></script>
<script	src="/angular-app/common/js/jsonrpc.js"></script>
<script	src="/angular-app/common/js/services.js"></script>
<script	src="/angular-app/userprofile/js/app.js"></script>