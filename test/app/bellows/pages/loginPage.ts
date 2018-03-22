import {browser, by, element} from 'protractor';

export class BellowsLoginPage {
  private readonly constants = require('../../testConstants');

  get() {
    browser.get(browser.baseUrl + '/auth/login');
  }

  form = element(by.id('login-loginForm'));
  infoMessages = element.all(by.className('alert-info'));
  errors = element.all(by.css('.alert-danger'));
  username = element(by.id('username'));
  password = element(by.id('password'));
  forgotPasswordLink = element(by.id('forgot_password'));
  submit     = element(by.id('login-submit'));

  login(username: string, password: string) {
    browser.get(browser.baseUrl + '/auth/logout');

    this.get();
    this.username.sendKeys(username);
    this.password.sendKeys(password);
    this.submit.click();
  }

  loginAsAdmin() {
    this.login(this.constants.adminEmail, this.constants.adminPassword);
  }

  loginAsManager() {
    this.login(this.constants.managerEmail, this.constants.managerPassword);
  }

  loginAsUser() {
    this.login(this.constants.memberEmail, this.constants.memberPassword);
  }

  loginAsMember = this.loginAsUser;

  loginAsSecondUser() {
    this.login(this.constants.member2Email, this.constants.member2Password);
  }

  loginAsSecondMember = this.loginAsSecondUser;

  loginAsObserver() {
    this.login(this.constants.observerEmail, this.constants.observerPassword);
  }

  logout() {
    browser.get(browser.baseUrl + '/auth/logout');
  }
}
