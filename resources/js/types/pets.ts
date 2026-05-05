export type Animal = {
    name: string;
    caloriesPerDay: number;
    attentionPoints: number;
    svgPath: string;
};

export type Pet = {
    id: number;
    name: string;
    birthday: string;
    calorieLevel: number;
    attentionLevel: number;
    animal: Animal;
};
