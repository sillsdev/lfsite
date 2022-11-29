import {browser, by, element, ExpectedConditions, protractor} from 'protractor';
import {UserManagementPage} from './user-management.page';
import {Utils} from './utils';

export class ProjectsPage {
  private readonly utils = new Utils();
  private readonly userManagementPage = new UserManagementPage();

  url = '/app/projects';
  get() {
    return browser.get(browser.baseUrl + this.url);
  }

  testProjectName = 'Test Project';
  createBtn = element(by.id('startJoinProjectButton'));
  // Or just select "100" from the per-page dropdown, then you're pretty much guaranteed the Test
  // Project will be on page 1, and you can find it.
  itemsPerPageCtrl = element(by.model('itemsPerPage'));
  projectsList = element.all(by.repeater('project in visibleProjects'));
  projectNames = element.all(by.repeater('project in visibleProjects').column('project.projectName'));

  findProject(projectName: string) {
    let foundRow: any;
    const result = protractor.promise.defer();
    const searchName = new RegExp(projectName);
    this.projectsList.map((row: any) => {
      row.getText().then((text: string) => {
        if (searchName.test(text)) {
          foundRow = row;
        }
      });
    }).then(() => {
      if (foundRow) {
        result.fulfill(foundRow);
      } else {
        result.reject('Project ' + projectName + ' not found.');
      }
    });

    return result.promise;
  }

  clickOnProject(projectName: string) {
    return this.findProject(projectName).then((projectRow: any) => {
      const projectLink = projectRow.element(by.css('a'));
      projectLink.getAttribute('href').then((url: string) => {
        browser.get(url);
      });
    });
  }

  settingsBtn = element(by.id('settingsBtn'));
  userManagementLink = (browser.baseUrl.includes('languageforge')) ?
    element(by.id('userManagementLink')) : element(by.id('dropdown-project-settings'));

  addUserToProject(projectName: any, usersName: string, roleText: string) {
    return this.findProject(projectName).then(async (projectRow: any) => {
      const projectLink = projectRow.element(by.css('a'));
      await projectLink.getAttribute('href').then((href: string) => {
        const results = /app\/lexicon\/([0-9a-fA-F]+)\//.exec(href);
        expect(results).not.toBeNull();
        expect(results.length).toBeGreaterThan(1);
        const projectId = results[1];
        UserManagementPage.get(projectId);
      });

      await browser.wait(ExpectedConditions.visibilityOf(this.userManagementPage.addMembersBtn), Utils.conditionTimeout);
      await this.userManagementPage.addMembersBtn.click();
      await browser.wait(ExpectedConditions.visibilityOf(this.userManagementPage.userNameInput), Utils.conditionTimeout);
      await this.userManagementPage.userNameInput.sendKeys(usersName);

      await this.utils.findRowByText(this.userManagementPage.typeaheadItems, usersName).then((item: any) => {
        item.click();
      });

      // This should be unique no matter what
      await this.userManagementPage.newMembersDiv.element(by.id('addUserButton')).click();

      // Now set the user to member or manager, as needed
      let foundUserRow: any;
      await this.userManagementPage.projectMemberRows.map((row: any) => {
        const nameColumn = row.element(by.binding('user.username'));
        nameColumn.getText().then((text: string) => {
          if (text === usersName) {
            foundUserRow = row;
          }
        });
      }).then(() => {
        if (foundUserRow) {
          const select = foundUserRow.element(by.css('select'));
          Utils.clickDropdownByValue(select, roleText);
        }
      });

      return this.get(); // After all is finished, reload projects page
    });
  }

  //noinspection JSUnusedGlobalSymbols
  addManagerToProject(projectName: string, usersName: string) {
    return this.addUserToProject(projectName, usersName, 'Manager');
  }

  addMemberToProject(projectName: string, usersName: string) {
    return this.addUserToProject(projectName, usersName, 'Contributor');
  }

  removeUserFromProject(projectName: string, userName: string) {
    return this.findProject(projectName).then(async (projectRow: any) => {
      const projectLink = projectRow.element(by.css('a'));
      await projectLink.getAttribute('href').then((href: string) => {
        const results = /app\/lexicon\/([0-9a-fA-F]+)\//.exec(href);
        expect(results).not.toBeNull();
        expect(results.length).toBeGreaterThan(1);
        const projectId = results[1];
        return UserManagementPage.get(projectId);
      });
      await browser.wait(ExpectedConditions.visibilityOf(this.userManagementPage.addMembersBtn), Utils.conditionTimeout);

      let userFilter: any;
      let projectMemberRows: any;
      userFilter = element(by.model('$ctrl.userFilter'));
      await userFilter.sendKeys(userName);
      projectMemberRows = element.all(by.repeater('user in $ctrl.list.visibleUsers'));

      const foundUserRow = await projectMemberRows.first();
      const rowCheckbox = foundUserRow.element(by.css('input[type="checkbox"]'));
      await this.utils.setCheckbox(rowCheckbox, true);
      const removeMembersBtn = element(by.id('remove-members-button'));
      await removeMembersBtn.click();

      return this.get(); // After all is finished, reload projects page
    });
  }
}
