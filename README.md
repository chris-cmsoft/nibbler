# Nibbler

Nibbler is a Laravel application for showcasing Laravel Cloud features through a playful, collaborative virtual pet experience. The product goal is to make infrastructure concepts visible and memorable by wrapping them in animated animals, similar in spirit to a Tamagotchi.

Teams log in to care for a shared collection of pets. Any team member can feed a pet, give it attention, or watch its status change in real time as other teammates interact with it.

## Product Intent

Nibbler exists to demonstrate Laravel Cloud capabilities through normal application behavior:

- Scheduled tasks automatically feeding pets at configured times.
- Queued jobs processing feeding work when workers pick them up.
- Websocket updates keeping every team member's UI in sync.
- Deployments introducing new adoptable pets through SVG assets shipped with the repository.

The application should feel like a useful demo of Laravel Cloud, not a static marketing page. Each cloud feature should be tied to something visible in the pet experience.

## Core Experience

Each team owns a set of pets. Team members can open the UI and see their pets in a simple list of cards. Each card shows the animal, its current state, and controls for feeding or giving attention.

Pets are animated animals stored as SVG files in the repository. Each pet also ships with a metadata JSON file containing its name, calorie-per-day need, and attention requirement. Because pet artwork and metadata ship with the application code, new releases can add new animals. Teams can then adopt those animals from an adoption market after the release is deployed.

## Pet Care Model

Every pet tracks calories and fulfillment.

Calories slowly decrease over time. Different animals burn calories at different rates, so each pet may need a different feeding interval. Underfeeding makes a pet unhealthy, but overfeeding does too.

Fulfillment changes through attention. Petting an animal increases fulfillment, while too little attention leaves it understimulated. Too much interaction makes it overstimulated. This can be represented through an interaction counter that measures whether a pet is within its healthy attention range.

## Scheduling And Queues

Teams can configure feeding schedules from the UI. When a schedule is due, Laravel's scheduler should add a feed job to the queue. The queue worker then processes that job and applies the feeding behavior to the pet.

Feeding jobs should take three minutes of real time to complete. Instead of adding all calories at once, a job should increment the pet's calorie counter a little bit every few seconds and broadcast those updates to the frontend as it works. In the product metaphor this avoids gluttony, but the main purpose is to showcase longer-running queue jobs and make their progress visible in the UI.

Because each feeding job occupies a worker for the full three minutes, other pets should wait for food when the queue is backed up. Scaling up queue workers should make that waiting time shrink, giving the demo a concrete way to show how worker capacity affects queued work.

This flow is intentionally part of the demo: users should be able to see that scheduled work and queued work are separate steps that cooperate to keep the pets cared for.

## Realtime Collaboration

Pet care is shared across the whole team. If one team member feeds or pets an animal, the frontend should update for every other team member through websockets. The animation and pet data should move together so the shared state feels alive and trustworthy.

## Adoption Market

The adoption market lets teams add more pets to their collection. It also gives the project a natural release path: new animal SVGs and metadata JSON files can be added to the repository over time, deployed with the application, and made available for adoption without changing the core care loop.

## Guiding Principles

- Keep the UI simple and centered on the pets.
- Make Laravel Cloud behavior visible through real product workflows.
- Treat scheduled work, queued work, realtime updates, and deployments as first-class parts of the experience.
- Let teams collaborate naturally without needing to refresh or coordinate manually.
- Add pets incrementally through software releases.
