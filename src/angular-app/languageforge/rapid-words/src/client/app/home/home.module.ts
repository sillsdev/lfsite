import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HomeComponent } from './home.component';
import { HomeRoutingModule } from './home-routing.module';
import { SharedModule } from '../shared/shared.module';
import { NameListService } from '../shared/name-list/name-list.service';
import { SemanticDomainListService } from '../shared/main-view/main-view.service';
import { MeaningDetailsModule } from '../meaning-details/meaning-details.module';

@NgModule({
  imports: [CommonModule, HomeRoutingModule, SharedModule, MeaningDetailsModule],
  declarations: [HomeComponent],
  exports: [HomeComponent],
  providers: [SemanticDomainListService]
})
export class HomeModule { }
