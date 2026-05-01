# RBAC + API Unified Auth Uitvoerbaar Plan

Doel
- Van single-admin gedrag naar multi-user platform met customers (Plesk-achtig).
- Eenzelfde RBAC-model afdwingen op web en API.
- Geen breaking change voor bestaande integraties tijdens migratie.

Niet-doelen (nu)
- Volledige billing/subscription automation.
- Volledige tenant-isolatie op infrastructuurlaag.

## Gewenste eindsituatie
- Elke actor is een User met rollen en permissies.
- Zowel web als API gebruiken dezelfde identity en authorisatiebron.
- Elke mutatie endpoint heeft expliciete autorisatiecheck.
- Resource ownership (bijv. sites/servers van customer X) is afgedwongen in policies/scopes.
- Auditing registreert wie wat deed via web of API.

## Fase 0 - Beslissingen en contract (2-3 dagen)
Taken
- Bevestig accountmodel:
  - Primary: User model als enige identity voor web + API.
  - Secondary: Service accounts als aparte capability op User tokens (abilities), niet via los Auth model.
- Bevestig rolset v1:
  - Super Admin, Admin, Reseller, Customer.
- Bevestig permission matrix v1 per domein:
  - server.*, site.*, user.*, role.*, permission.*, module.*, backup.*, email.*, ftp.*, database.*, dns.*, ssl.*, audit.*.
- Bevestig ownership model:
  - Customer ziet en beheert alleen eigen resources.
  - Reseller ziet alleen eigen klanten/resources.

Deliverables
- Permission matrix document.
- Resource ownership regels per model.
- Beslissing: deprecatiepad voor huidige cipi token-flow.

Acceptatiecriteria
- Team akkoord op matrix en ownership regels.
- Scope v1 vastgezet voor implementatie.

## Fase 1 - Fundament voor gedeelde auth (4-6 dagen)
Taken
- Introduceer uniforme API-auth via Sanctum tokens op User.
- Voeg token abilities toe (bijv. api.read, api.write, site.manage).
- Maak compatibiliteitslaag voor bestaande clients:
  - Huidige cipi.auth blijft tijdelijk bestaan achter feature flag.
  - Nieuwe middleware prefereert User Sanctum token.
- Bouw request context:
  - Authenticated user moet altijd beschikbaar zijn voor policies en audit logs.

Technische acties
- Nieuwe middleware: EnsureApiUserAuthenticated.
- Nieuwe helper/service: token ability mapper -> Spatie permissions.
- Feature flag in config voor dual-stack auth.

Acceptatiecriteria
- API request met User token heeft geldige request user context.
- Oude clients blijven werken zolang feature flag aan staat.

## Fase 2 - RBAC afdwingen op weblaag (4-7 dagen)
Taken
- Bescherm settings en beheerpagina's met can middleware op route niveau.
- Voeg server-side checks toe in Livewire acties (create/update/delete user/role).
- Bescherm modulebeheer, file manager en overige mutaties met expliciete checks.

Technische acties
- Route middleware per sectie/action:
  - users: user.view/user.create/user.edit/user.delete
  - roles: role.view/role.create/role.edit/role.delete + permission.assign
- Livewire guard pattern:
  - abort_if of authorize voor elke muterende methode.
- Verbied privilege-escalatie:
  - Niet je eigen hoogste rol verwijderen/downgraden.
  - Core roles beschermd tegen delete/rename tenzij super admin policy dit toelaat.

Acceptatiecriteria
- Niet-geautoriseerde user krijgt 403 op backend, ook bij handmatige requests.
- UI-verbergen en backend-authorisatie zijn consistent.

## Fase 3 - RBAC afdwingen op API (6-10 dagen)
Taken
- Voeg authorize/policy checks toe aan alle API mutaties en uitlees-endpoints.
- Zet endpoint permissies expliciet op route of controller niveau.
- Voer resource-scope filtering af op basis van ownership.

Technische acties
- Policy klassen voor minimaal:
  - ServerPolicy, SitePolicy, UserPolicy, RolePolicy, ModulePolicy, BackupPolicy.
- Endpoint mapping voorbeeld:
  - GET /api/sites -> site.view + ownership scope.
  - POST /api/sites -> site.create + ownership target check.
  - PATCH /api/sites/{id} -> site.edit op specifiek model.
- Centrale response standaard voor 403/404 om datalek via errors te beperken.

Acceptatiecriteria
- Alle gevoelige endpoints blokkeren correct zonder vereiste permissie.
- Customer token kan nooit resources van andere tenants lezen of wijzigen.

## Fase 4 - Datamodel en tenancy-lite (5-8 dagen)
Taken
- Leg ownership vast op modellen die nu globaal zijn.
- Voeg relatiemodel toe voor customer-reseller-hiërarchie.

Technische acties (minimaal)
- Tabellen/kolommen:
  - users: account_type, owner_user_id nullable.
  - servers/sites/etc: owner_user_id of account_id.
- Query scopes:
  - visibleTo(user) op kernmodellen.
- Migraties + backfill script voor bestaande data.

Acceptatiecriteria
- Zichtbaarheid en mutaties volgen ownership regels in web en API.
- Backfill voltooid zonder dataverlies.

## Fase 5 - Auditing, observability, hardening (3-5 dagen)
Taken
- Uniforme audit events voor role/permission/resource mutaties.
- Beveiligingshardening op token lifecycle.

Technische acties
- Log bron: web of api, actor user id, token id, target resource.
- Token policy:
  - Expiry, rotatie, revoke endpoint, least-privilege abilities.
- Rate limiting per actor + gevoelige endpoints.

Acceptatiecriteria
- Security events zijn traceerbaar per actor en endpoint.
- Revoked token verliest direct toegang.

## Fase 6 - Testplan en rollout (4-6 dagen)
Taken
- Bouw regressietests en autorisatietests.
- Gefaseerde productie rollout met fallback.

Testmatrix
- Web:
  - Customer kan alleen eigen assets beheren.
  - Reseller kan eigen klanten beheren, niet globale admins.
  - Admin/Super Admin flows.
- API:
  - 401 zonder token.
  - 403 met token zonder permission.
  - 200 met correcte permission + ownership.
- Negatief:
  - Role escalation pogingen.
  - Cross-tenant resource access.

Rollout
- Stap 1: Dual auth aan, logging-only mode voor denied checks (optioneel 48 uur).
- Stap 2: Enforce mode aan voor low-risk domeinen.
- Stap 3: Enforce mode aan voor alle mutaties.
- Stap 4: Oude cipi auth uitfaseren.

Acceptatiecriteria
- Kritieke autorisatietests groen.
- Geen P1 regressies in eerste rolloutwindow.

## Werkpakket indeling (sprintbaar)
Sprint A
- Fase 0 + Fase 1
- Deliverable: werkende User token auth op API met feature flag.

Sprint B
- Fase 2
- Deliverable: settings/livewire/controller mutaties backend-afgedwongen.

Sprint C
- Fase 3
- Deliverable: policy enforcement op API endpoints.

Sprint D
- Fase 4 + Fase 5
- Deliverable: ownership model + audit hardening.

Sprint E
- Fase 6
- Deliverable: volledige testdekking + productie rollout.

## Directe first actions (deze week)
1. Maak permission matrix v1 en ownershipregels definitief.
2. Voeg feature flag toe voor dual API auth.
3. Implementeer nieuwe API middleware met User context.
4. Bescherm settings routes + Livewire mutaties met can checks.
5. Schrijf eerste autorisatietests voor users/roles settings en sites API.

## Risico's en mitigatie
- Risico: breaking changes voor bestaande API clients.
  - Mitigatie: dual auth fase + telemetrie + deadline communicatie.
- Risico: incomplete ownership op legacy data.
  - Mitigatie: backfill scripts + dry-run rapport + rollback plan.
- Risico: verborgen endpoints zonder checks.
  - Mitigatie: endpoint inventory + verplicht policy gate in code review checklist.

## Definition of Done
- Web en API delen hetzelfde identity + RBAC model.
- Geen muterend endpoint zonder expliciete authorisatie.
- Tenancy ownership afgedwongen op read en write.
- Audit trail compleet en doorzoekbaar.
- Legacy auth uitgefaseerd of expliciet beperkt tot service-only pad.
