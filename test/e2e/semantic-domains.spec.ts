import { expect } from '@playwright/test';
import { test } from './utils/fixtures';

import { ProjectsPage } from './pages/projects.page';

import { Project } from './utils/types';


import constants from './testConstants.json';

// import {browser, ExpectedConditions} from 'protractor';

// import {BellowsLoginPage} from '../../../bellows/shared/login.page';
// import {PageHeader} from '../../../bellows/shared/page-header.element';
// import {ProjectsPage} from '../../../bellows/shared/projects.page';
// import {Utils} from '../../../bellows/shared/utils';
// import {EditorPage} from '../shared/editor.page';
// import {ProjectSettingsPage} from '../shared/project-settings.page';

test.describe('Lexicon E2E Semantic Domains Lazy Load', () => {
  let projectsPageManager: ProjectsPage;
  const project: Project = {
    name: 'semantic_domainsprojects_spec_ts Project 04',
    code: 'p04_projects_spec_ts__project_04',
    id: ''
  };
  /*
  const editorPage   = new EditorPage();
  const header = new PageHeader();
  const loginPage = new BellowsLoginPage();
  const projectsPage = new ProjectsPage();
  const projectSettingsPage = new ProjectSettingsPage();

  const semanticDomain1dot1English = constants.testEntry1.senses[0].semanticDomain.values[0] + ' Sky';
  const semanticDomain1dot1Thai = constants.testEntry1.senses[0].semanticDomain.values[0] + ' ท้องฟ้า';

  test('should be using English Semantic Domain for manager', async () => {
    await loginPage.loginAsManager();
    await projectsPage.get();
    await projectsPage.clickOnProject(constants.testProjectName);
    await editorPage.edit.toListLink.click();
    await editorPage.browse.clickEntryByLexeme(constants.testEntry1.lexeme.th.value);
    await browser.wait(ExpectedConditions.visibilityOf(await editorPage.edit.fields.last()), Utils.conditionTimeout);
    expect<any>(await editorPage.edit.getFirstLexeme()).toEqual(constants.testEntry1.lexeme.th.value);
    expect<any>(await editorPage.edit.semanticDomain.values.first().getText()).toEqual(semanticDomain1dot1English);
    expect<any>(await header.language.button.getText()).toEqual('English');
  });

  test('can change Project default language to Thai', async () => {
    await projectSettingsPage.getByLink();
    expect<any>(await projectSettingsPage.tabs.project.isDisplayed()).toBe(true);
    expect<any>(await projectSettingsPage.projectTab.saveButton.isDisplayed()).toBe(true);
    expect<any>(await projectSettingsPage.projectTab.defaultLanguageSelect.isDisplayed()).toBe(true);
    expect<any>(await projectSettingsPage.projectTab.defaultLanguageSelected.getText()).toContain('English');
    await projectSettingsPage.projectTab.defaultLanguageSelect.sendKeys('ภาษาไทย');
    await projectSettingsPage.projectTab.saveButton.click();
    expect<any>(await projectSettingsPage.projectTab.defaultLanguageSelected.getText()).toContain('ภาษาไทย');
    expect<any>(await header.language.button.getText()).toEqual('ภาษาไทย');
  });

  test('should be using Thai Semantic Domain', async () => {
    await Utils.clickBreadcrumb(constants.testProjectName);
    await editorPage.edit.toListLink.click();
    await editorPage.browse.clickEntryByLexeme(constants.testEntry1.lexeme.th.value);
    await browser.wait(ExpectedConditions.visibilityOf(await editorPage.edit.fields.last()), Utils.conditionTimeout);
    expect<any>(await editorPage.edit.semanticDomain.values.first().getText()).toEqual(semanticDomain1dot1Thai);
  });

  test('can change Project default language back to English', async () => {
    await projectSettingsPage.getByLink();
    expect<any>(await projectSettingsPage.projectTab.defaultLanguageSelected.getText()).toContain('ภาษาไทย');
    await projectSettingsPage.projectTab.defaultLanguageSelect.sendKeys('English');
    await projectSettingsPage.projectTab.saveButton.click();
    expect<any>(await projectSettingsPage.projectTab.defaultLanguageSelected.getText()).toContain('English');
    expect<any>(await header.language.button.getText()).toEqual('English');
  });

  test('should be using English Semantic Domain', async () => {
    await Utils.clickBreadcrumb(constants.testProjectName);
    await editorPage.edit.toListLink.click();
    await editorPage.browse.clickEntryByLexeme(constants.testEntry1.lexeme.th.value);
    await browser.wait(ExpectedConditions.visibilityOf(await editorPage.edit.fields.last()), Utils.conditionTimeout);
    expect<any>(await editorPage.edit.semanticDomain.values.first().getText()).toEqual(semanticDomain1dot1English);
  });

  test('can change Project default language back to Thai', async () => {
    await browser.refresh();
    await browser.wait(ExpectedConditions.visibilityOf(editorPage.edit.entryCountElem), Utils.conditionTimeout);
    await projectSettingsPage.getByLink();
    expect<any>(await projectSettingsPage.projectTab.defaultLanguageSelected.getText()).toContain('English');
    await projectSettingsPage.projectTab.defaultLanguageSelect.sendKeys('ภาษาไทย');
    await projectSettingsPage.projectTab.saveButton.click();
    expect<any>(await projectSettingsPage.projectTab.defaultLanguageSelected.getText()).toContain('ภาษาไทย');
  });

  test('should be using Thai Semantic Domain after refresh', async () => {
    await Utils.clickBreadcrumb(constants.testProjectName);
    await editorPage.edit.toListLink.click();
    await editorPage.browse.clickEntryByLexeme(constants.testEntry1.lexeme.th.value);
    expect<any>(await editorPage.edit.semanticDomain.values.first().getText()).toEqual(semanticDomain1dot1Thai);
    expect<any>(await editorPage.edit.entryCountElem.isDisplayed()).toBe(true);
    await browser.refresh();
    await browser.wait(ExpectedConditions.visibilityOf(editorPage.edit.entryCountElem), Utils.conditionTimeout);
    expect<any>(await editorPage.edit.semanticDomain.values.first().getText()).toEqual(semanticDomain1dot1Thai);
  });

  test('can change user interface language', async () => {
    expect<any>(await header.language.button.getText()).toEqual('ภาษาไทย');
    await header.language.button.click();
    await header.language.findItem('English').click();
    expect<any>(await header.language.button.getText()).toEqual('English');
  });

  test('should still have Thai for Project default language', async () => {
    await projectSettingsPage.getByLink();
    expect<any>(await projectSettingsPage.projectTab.defaultLanguageSelected.getText()).toContain('ภาษาไทย');
  });

  test('should be using English Semantic Domain', async () => {
    await Utils.clickBreadcrumb(constants.testProjectName);
    await editorPage.edit.toListLink.click();
    await editorPage.browse.clickEntryByLexeme(constants.testEntry1.lexeme.th.value);
    await browser.wait(ExpectedConditions.visibilityOf(await editorPage.edit.fields.last()), Utils.conditionTimeout);
    expect<any>(await editorPage.edit.semanticDomain.values.first().getText()).toEqual(semanticDomain1dot1English);
  });

  test('should be using English Semantic Domain after refresh', async () => {
    expect<any>(await editorPage.edit.entryCountElem.isDisplayed()).toBe(true);
    await browser.refresh();
    await browser.wait(ExpectedConditions.visibilityOf(editorPage.edit.entryCountElem), Utils.conditionTimeout);
    expect<any>(await editorPage.edit.semanticDomain.values.first().getText()).toEqual(semanticDomain1dot1English);
  });

  test('should still have Thai for Project default language', async () => {
    await projectSettingsPage.getByLink();
    expect<any>(await projectSettingsPage.projectTab.defaultLanguageSelected.getText()).toContain('ภาษาไทย');
  });

  test('can change user interface language to English', async () => {
    expect<any>(await projectSettingsPage.projectTab.defaultLanguageSelected.getText()).toContain('ภาษาไทย');
    await header.language.button.click();
    await header.language.findItem('English').click();
    expect<any>(await header.language.button.getText()).toEqual('English');
  });

  test('can change Project default language to match interface language twice', async () => {
    await projectSettingsPage.projectTab.defaultLanguageSelect.sendKeys('English');
    await projectSettingsPage.projectTab.saveButton.click();
    expect<any>(await projectSettingsPage.projectTab.defaultLanguageSelected.getText()).toContain('English');
    expect<any>(await header.language.button.getText()).toEqual('English');

    await projectSettingsPage.projectTab.defaultLanguageSelect.sendKeys('ภาษาไทย');
    await projectSettingsPage.projectTab.saveButton.click();
    expect<any>(await projectSettingsPage.projectTab.defaultLanguageSelected.getText()).toContain('ภาษาไทย');
    expect<any>(await header.language.button.getText()).toEqual('ภาษาไทย');
  });

  test('can change user interface language to back English', async () => {
    await header.language.button.click();
    await header.language.findItem('English').click();
    expect<any>(await header.language.button.getText()).toEqual('English');
  });
*/
});
